<?php
namespace API\Request;

use Func\Key;
use Func\Merkle;
use System\API;
use System\Config;
use Util\Logger;
use Util\Memcached;

class Genesis extends API {

    private $key;
    private $merkle;
    private $store;
    private $memcached;

    private $m_private_key;
    private $m_public_key;
    private $m_address;

    private $m_timestamp;

    private $m_transactions = [];
    private $m_balances = [];
    private $m_deposits = [];
    private $m_attributes = [];

    private $m_block;
    private $m_tracker;

    private $m_transaction_hash;
    private $m_block_hash;

    function __construct()
    {
        $this->key = new Key();
        $this->merkle = new Merkle();
        $this->store = new \Store\Genesis();
        $this->memcached = new Memcached();

        $this->m_timestamp = Logger::Microtime();
    }

    function _process()
    {
        $this->CheckGenesis();
        $this->CreateKey();
        $this->CreateGenesisTransaction();
        $this->CreateGenesisBalance();
        $this->CreateGenesisDeposit();
        $this->CreateGenesisAttribute();
        $this->CreateGenesisBlock();
        $this->CreateGenesisTracker();
        $this->MakeDatabases();
        $this->MakeGenesisData();
    }

    function MakeDatabases() {
        $this->store->MakeDatabases();
    }

    function MakeGenesisData() {
        $this->store->MakeGenesisData($this->m_transactions, $this->m_attributes, $this->m_balances, $this->m_deposits, $this->m_block, $this->m_tracker);
    }

    function CreateGenesisTransaction() {
        $transaction_genesis = array(
            'version' => Config::$version,
            'type' => Config::$transaction_type_genesis,
            'public_key' => $this->m_public_key,
            'from' => $this->m_address,
            'to' => $this->m_address,
            'value' => Config::$genesis_coin_value,
            'fee' => 0,
            'timestamp' => $this->m_timestamp,
        );

        $transaction_deposit = array(
            'version' => Config::$version,
            'type' => Config::$transaction_type_deposit,
            'public_key' => $this->m_public_key,
            'from' => $this->m_address,
            'to' => $this->m_address,
            'value' => Config::$genesis_deposit_value,
            'fee' => 0,
            'timestamp' => $this->m_timestamp,
        );

        $thash_genesis = hash('sha256', json_encode($transaction_genesis));
        $signature_genesis = $this->key->MakeSignature($thash_genesis, $this->m_private_key, $this->m_public_key);
        $transaction_genesis['thash'] = $thash_genesis;
        $transaction_genesis['signature'] = $signature_genesis;
        $transaction_genesis['result'] = 'accept';

        $thash_deposit = hash('sha256', json_encode($transaction_deposit));
        $signature_deposit = $this->key->MakeSignature($thash_deposit, $this->m_private_key, $this->m_public_key);
        $transaction_deposit['thash'] = $thash_deposit;
        $transaction_deposit['signature'] = $signature_deposit;
        $transaction_deposit['result'] = 'accept';

        $this->m_transactions[] = $transaction_genesis;
        $this->m_transactions[] = $transaction_deposit;

        $this->m_transaction_hash = $this->merkle->MakeMerkleHash($this->m_transactions);
        $this->data['transactions'] = $this->m_transactions;
    }

    function CreateGenesisBalance() {
        $this->m_balances[] = array(
            'address' => $this->m_address,
            'value' => Config::$genesis_coin_value - Config::$genesis_deposit_value,
        );
    }

    function CreateGenesisDeposit() {
        $this->m_deposits[] = array(
            'address' => $this->m_address,
            'value' => Config::$genesis_deposit_value,
        );
    }

    function CreateGenesisAttribute() {
        $this->m_attributes[] = array(
            'address' => $this->m_address,
            'key' => 'role',
            'value' => 'validator',
        );
    }

    function CreateGenesisBlock() {
        $genesis_hash = hash('sha256', Config::$genesis_key);
        $this->m_block_hash = $this->merkle->MakeBlockHash($genesis_hash, $this->m_transaction_hash);

        $this->m_block = array(
            'last_block' => $genesis_hash,
            'block' => $this->m_block_hash,
            'transaction_count' => count($this->m_transactions),
            'timestamp' => $this->m_timestamp,
        );

        foreach ($this->m_transactions as $k => $transaction) {
            $this->m_transactions[$k]['status'] = 'end';
            $this->m_transactions[$k]['block'] = $this->m_block_hash;
        }
    }

    function CreateGenesisTracker() {
        $this->m_tracker = array(
            'host' => $_SERVER['HTTP_HOST'],
            'address' => Config::$genesis_address,
        );
    }

    function CreateKey() {
        $node_key = [];

        $this->m_private_key = Config::$genesis_private_key;
        $this->m_public_key = Config::$genesis_public_key;
        $this->m_address = Config::$genesis_address;

//        $this->m_private_key = $this->key->MakePrivateKey();
//        $this->m_public_key = $this->key->MakePublicKey($this->m_private_key);
//        $this->m_address = $this->key->MakeAddress($this->m_public_key);

        $node_key['private_key'] = $this->m_private_key;
        $node_key['public_key'] = $this->m_public_key;
        $node_key['address'] = $this->m_address;

        $this->data['node_key'] = $node_key;
    }

    function CheckGenesis() {
        # Check genesis request in memcached
        # Check genesisBlock

        $v = $this->memcached->get('CheckGenesis');

        if ($v === false) {
            $this->memcached->set('CheckGenesis', 'inProcess', Config::$genesis_request_cache_interval);
        } else {
            $this->Error('There is genesis block already ');
        }

        if ($this->store->CheckGenesisBlock() === true)
            $this->Error('There is genesis block already ');
    }

}