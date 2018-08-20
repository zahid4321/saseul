<?php

namespace src\Func;

use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Query;
use src\System\Config;
use src\System\Database;

class Consensus
{
    private $db;
    private $merkle;

    private $m_bulk;
    private $m_query;

    private $m_transactions_for_commit;
    private $m_balances;
    private $m_deposits;
    private $m_attributes;
    private $m_check_address;
    private $m_fees;

    private $m_last_blockhash;
    private $m_transaction_hash;
    private $m_blockhash;

    private $m_block;

    function __construct()
    {
        $this->db = new Database();
        $this->merkle = new Merkle();
    }

    function GetBlockhash($timestamp, $max_count = 30000)
    {
        $this->m_transactions_for_commit = [];

        $this->GetConsensusTransaction($timestamp, $max_count);
        $this->GetLastBlockhash();
        $this->MakeTransactionHash();
        $this->MakeBlockhash();

        return $this->m_blockhash;
    }

    function MakeBlockhash()
    {
        $this->m_blockhash = $this->merkle->MakeBlockHash($this->m_last_blockhash, $this->m_transaction_hash);
    }

    function MakeTransactionHash()
    {
        $transactions = [];

        foreach ($this->m_transactions_for_commit as $t) {
            $transaction = [];

            foreach ($t as $key => $value) {
                if (in_array($key, ['_id', 'block', 'status']))
                    continue;

                $transaction[$key] = $value;
            }

            $transactions[] = $transaction;
        }

        $this->m_transaction_hash = $this->merkle->MakeMerkleHash($transactions);
    }

    function GetLastBlockhash()
    {
        $this->m_query = new Query([], ['sort' => ['timestamp' => -1]]);
        $namespace = Config::$database_mongodb_name_committed . '.blocks';
        $rs = $this->db->m_manager->executeQuery($namespace, $this->m_query);

        foreach ($rs as $item) {
            $this->m_last_blockhash = $item->block;
            break;
        }
    }

    function PreCommit($timestamp, $max_count = 30000)
    {
        $this->m_transactions_for_commit= [];
        $this->m_balances = [];
        $this->m_deposits = [];
        $this->m_check_address = [];
        $this->m_fees = 0;

        $this->GetPreCommitTransaction($timestamp, $max_count);
        $this->GetBalances();
        $this->GetDeposits();
        $this->ProcessPreCommitTransaction();
        $this->ProcessPreCommitAttribute();
        $this->ProcessPreCommitBalance();
        $this->ProcessPreCommitDeposit();
    }

    function ConsensusCommit($timestamp, $max_count = 30000)
    {
        $this->m_transactions_for_commit= [];
        $this->m_balances = [];
        $this->m_deposits = [];
        $this->m_check_address = [];
        $this->m_fees = 0;

        $this->GetConsensusTransaction($timestamp, $max_count);
        $this->GetBalances();
        $this->GetDeposits();
        $this->ProcessDeletePreCommit();
        $this->ProcessConsensusTransaction();
        $this->ProcessConsensusAttribute();
        $this->ProcessConsensusBalance();
        $this->ProcessConsensusDeposit();
        $this->MakeBlock($timestamp);
    }

    function MakeBlock($timestamp) {
        $this->m_block = array(
            'last_block' => $this->m_last_blockhash,
            'block' => $this->m_blockhash,
            'transaction_count' => count($this->m_transactions_for_commit),
            'timestamp' => $timestamp,
        );

        $this->m_bulk = new BulkWrite();
        $this->m_bulk->insert($this->m_block);
        $namespace = Config::$database_mongodb_name_committed . '.blocks';
        $this->db->m_manager->executeBulkWrite($namespace, $this->m_bulk);
    }

    function ProcessBalance($type) {
        if (count($this->m_balances) == 0)
            return;

        $this->m_bulk = new BulkWrite();

        foreach ($this->m_balances as $address => $value) {
            $this->m_bulk->update(['address' => $address], ['address' => $address, 'value' => $value], ['upsert' => true]);
        }

        switch ($type) {
            case 'consensus':
                $namespace = Config::$database_mongodb_name_committed . '.balances';
                break;
            case 'precommit':
            default:
                $namespace = Config::$database_mongodb_name_precommit . '.balances';
                break;
        }

        $this->db->m_manager->executeBulkWrite($namespace, $this->m_bulk);
    }

    function ProcessPreCommitBalance()
    {
        $this->ProcessBalance('precommit');
    }

    function ProcessConsensusBalance()
    {
        $this->ProcessBalance('consensus');
    }

    function ProcessAttribute($type) {
        if (count($this->m_attributes) == 0)
            return;

        $this->m_bulk = new BulkWrite();

        foreach ($this->m_attributes as $address => $value) {
            $this->m_bulk->update(['address' => $address], ['address' => $address, 'key' => 'role', 'value' => $value], ['upsert' => true]);
        }

        switch ($type) {
            case 'consensus':
                $namespace = Config::$database_mongodb_name_committed . '.attributes';
                break;
            case 'precommit':
            default:
                $namespace = Config::$database_mongodb_name_precommit . '.attributes';
                break;
        }

        $this->db->m_manager->executeBulkWrite($namespace, $this->m_bulk);
    }

    function ProcessPreCommitAttribute() {
        $this->ProcessAttribute('precommit');
    }

    function ProcessConsensusAttribute() {
        $this->ProcessAttribute('consensus');
    }

    function ProcessDeposit($type) {
        if (count($this->m_deposits) == 0)
            return;

        $this->m_bulk = new BulkWrite();

        foreach ($this->m_deposits as $address => $value) {
            $this->m_bulk->update(['address' => $address], ['address' => $address, 'value' => $value], ['upsert' => true]);
        }

        switch ($type) {
            case 'consensus':
                $namespace = Config::$database_mongodb_name_committed . '.deposits';
                break;
            case 'precommit':
            default:
                $namespace = Config::$database_mongodb_name_precommit . '.deposits';
                break;
        }

        $this->db->m_manager->executeBulkWrite($namespace, $this->m_bulk);
    }

    function ProcessPreCommitDeposit()
    {
        $this->ProcessDeposit('precommit');
    }

    function ProcessConsensusDeposit()
    {
        $this->ProcessDeposit('consensus');
    }

    function ProcessTransactionSendCoin($type, $t) {
        if ($this->m_balances[$t['from']] >= ($t['value'] + $t['fee'])) {
            # minus balance
            $this->m_balances[$t['from']] = $this->m_balances[$t['from']] - ($t['value'] + $t['fee']);

            # plus balance
            if (isset($this->m_balances[$t['to']]))
                $this->m_balances[$t['to']] = $this->m_balances[$t['to']] + $t['value'];
            else
                $this->m_balances[$t['to']] = $t['value'];

            # plus fee
            $this->m_fees = $this->m_fees + $t['fee'];

            # update accept
            $this->ProcessTransactionAccept($type, $t);
        } else {
            # update denied
            $this->ProcessTransactionDenied($type, $t);
        }
    }

    function ProcessTransactionDeposit($type, $t) {
        if ($this->m_balances[$t['from']] >= ($t['value'] + $t['fee'])) {
            # minus balance
            $this->m_balances[$t['from']] = $this->m_balances[$t['from']] - ($t['value'] + $t['fee']);

            # plus deposit
            if (isset($this->m_deposits[$t['from']]))
                $this->m_deposits[$t['from']] = $this->m_deposits[$t['from']] + $t['value'];
            else
                $this->m_deposits[$t['from']] = $t['value'];

            # plus fee
            $this->m_fees = $this->m_fees + $t['fee'];

            # update accept
            $this->ProcessTransactionAccept($type, $t);
        } else {
            # update denied
            $this->ProcessTransactionDenied($type, $t);
        }
    }

    function ProcessTransactionWithDraw($type, $t) {
        if ($this->m_deposits[$t['from']] >= ($t['value'] + $t['fee'])) {
            # minus deposit
            $this->m_deposits[$t['from']] = $this->m_deposits[$t['from']] - ($t['value'] + $t['fee']);

            # plus balance
            if (isset($this->m_balances[$t['from']]))
                $this->m_balances[$t['from']] = $this->m_balances[$t['from']] + $t['value'];
            else
                $this->m_balances[$t['from']] = $t['value'];

            # plus fee
            $this->m_fees = $this->m_fees + $t['fee'];

            # update accept
            $this->ProcessTransactionAccept($type, $t);
        } else {
            # update denied
            $this->ProcessTransactionDenied($type, $t);
        }
    }

    function ProcessTransactionUpToSupervisor($type, $t) {
        # For Test
        if ($this->m_attributes[$t['from']] !== 'validator' || $this->m_attributes[$t['from']] !== 'supervisor') {
            $this->m_attributes[$t['from']] = 'supervisor';
            $this->ProcessTransactionAccept($type, $t);
        } else {
            # update denied
            $this->ProcessTransactionDenied($type, $t);
        }
    }

    function ProcessTransactionUpToValidator($type, $t) {
        # For Test
        if ($this->m_attributes[$t['from']] !== 'validator') {
            $this->m_attributes[$t['from']] = 'validator';
            $this->ProcessTransactionAccept($type, $t);
        } else {
            # update denied
            $this->ProcessTransactionDenied($type, $t);
        }
    }

    function ProcessTransactionAccept($type, $t) {
        switch ($type) {
            case 'consensus':
                unset($t['_id']);
                $t['result'] = 'accept';
                $t['status'] = 'end';
                $t['block'] = $this->m_blockhash;
                $this->m_bulk->insert($t);
                break;
            case 'precommit':
            default:
                $this->m_bulk->update(['status' => 'broadcast', '_id' => $t['_id']], ['$set' => ['status' => 'precommit', 'result' => 'accept']]);
                break;
        }
    }

    function ProcessTransactionDenied($type, $t) {
        switch ($type) {
            case 'consensus':
                unset($t['_id']);
                $t['result'] = 'denied';
                $t['status'] = 'end';
                $t['block'] = $this->m_blockhash;
                $this->m_bulk->insert($t);
                break;
            case 'precommit':
            default:
                $this->m_bulk->update(['status' => 'broadcast', '_id' => $t['_id']], ['$set' => ['status' => 'precommit', 'result' => 'denied']]);
                break;
        }
    }

    function ProcessTransaction($type)
    {
        if (count($this->m_transactions_for_commit) == 0)
            return;

        $this->m_bulk = new BulkWrite();

        foreach ($this->m_transactions_for_commit as $t) {
            switch ($t['type']) {
                case Config::$transaction_type_send_coin:
                    $this->ProcessTransactionSendCoin($type, $t);
                    break;
                case Config::$transaction_type_deposit:
                    $this->ProcessTransactionDeposit($type, $t);
                    break;
                case Config::$transaction_type_withdraw:
                    $this->ProcessTransactionWithDraw($type, $t);
                    break;
                case Config::$transaction_type_uptosupervisor:
                    $this->ProcessTransactionUpToSupervisor($type, $t);
                    break;
                case Config::$transaction_type_uptovalidator:
                    $this->ProcessTransactionUpToValidator($type, $t);
                    break;
            }
        }

        switch ($type) {
            case 'consensus':
                $namespace = Config::$database_mongodb_name_committed . '.transactions';
                break;
            case 'precommit':
            default:
            $namespace = Config::$database_mongodb_name_precommit . '.transactions';
                break;
        }

        $this->db->m_manager->executeBulkWrite($namespace, $this->m_bulk);
    }

    function ProcessDeletePreCommit() {
        if (count($this->m_transactions_for_commit) == 0)
            return;

        $this->m_bulk = new BulkWrite();

        foreach ($this->m_transactions_for_commit as $t) {
            $this->m_bulk->delete(['_id' => $t['_id']]);
        };

        $namespace = Config::$database_mongodb_name_precommit . '.transactions';
        $this->db->m_manager->executeBulkWrite($namespace, $this->m_bulk);
    }

    function ProcessPreCommitTransaction() {
        $this->ProcessTransaction('precommit');
    }

    function ProcessConsensusTransaction()
    {
        $this->ProcessTransaction('consensus');
    }

    function GetBalances()
    {
        $this->m_query = new Query(['address' => ['$in' => $this->m_check_address]]);
        $namespace = Config::$database_mongodb_name_committed . '.balances';
        $rs = $this->db->m_manager->executeQuery($namespace, $this->m_query);

        foreach ($rs as $item) {
            $this->m_balances[$item->address] = $item->value;
        }
    }

    function GetDeposits()
    {
        $this->m_query = new Query(['address' => ['$in' => $this->m_check_address]]);
        $namespace = Config::$database_mongodb_name_committed . '.deposits';
        $rs = $this->db->m_manager->executeQuery($namespace, $this->m_query);

        foreach ($rs as $item) {
            $this->m_deposits[$item->address] = $item->value;
        }
    }

    function GetAttributes() {
        $this->m_query = new Query(['address' => ['$in' => $this->m_check_address]]);
        $namespace = Config::$database_mongodb_name_committed . '.attributes';
        $rs = $this->db->m_manager->executeQuery($namespace, $this->m_query);

        foreach ($rs as $item) {
            $this->m_attributes[$item->address] = $item->value;
        }
    }

    function GetTransactionForCommit($type, $timestamp, $max_count = 30000)
    {
        switch ($type) {
            case 'consensus':
                $this->m_query = new Query(['status' => 'precommit', 'timestamp' => ['$lt' => $timestamp]], ['sort' => ['timestamp' => 1, 'thash' => 1]]);
                break;
            case 'precommit':
            default:
                $this->m_query = new Query(['timestamp' => ['$lt' => $timestamp]], ['sort' => ['timestamp' => 1, 'thash' => 1]]);
                break;
        }

        $namespace = Config::$database_mongodb_name_precommit . '.transactions';
        $rs = $this->db->m_manager->executeQuery($namespace, $this->m_query);
        $i = 0;

        foreach ($rs as $item) {
            $transaction = [];

            foreach ($item as $key => $value) {
                $transaction[$key] = $value;
            }

            $this->m_check_address[] = $transaction['from'];
            $this->m_transactions_for_commit[] = $transaction;
            $i++;

            if ($i >= $max_count)
                break;
        }

        $this->m_check_address = array_values(array_unique($this->m_check_address));
    }

    function GetPreCommitTransaction($timestamp, $max_count = 30000)
    {
        $this->GetTransactionForCommit('precommit', $timestamp, $max_count);
    }

    function GetConsensusTransaction($timestamp, $max_count = 30000)
    {
        $this->GetTransactionForCommit('consensus', $timestamp, $max_count);
    }
}