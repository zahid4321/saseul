<?php

namespace Store;

use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use System\Config;
use System\Database;

class Genesis
{
    private $db;

    function __construct()
    {
        $this->db = new Database();
    }

    function CheckGenesisBlock() {
        $rs = $this->db->m_manager->executeCommand(Config::$database_mongodb_name_committed , new Command(
            array('count' => 'blocks')
        ));

        $count = 0;

        foreach ($rs as $item) {
            $count = $item->n;
            break;
        }

        if ($count > 0)
            return true;
        else
            return false;
    }

    function MakeDatabases() {
        $this->db->m_manager->executeCommand(Config::$database_mongodb_name_consensus, new Command(array('create' => 'collect_chunk_meta')));
        $this->db->m_manager->executeCommand(Config::$database_mongodb_name_tracker, new Command(array('create' => 'validator')));
        $this->db->m_manager->executeCommand(Config::$database_mongodb_name_tracker, new Command(array('create' => 'supervisor')));
        $this->db->m_manager->executeCommand(Config::$database_mongodb_name_precommit, new Command(array('create' => 'transactions')));
        $this->db->m_manager->executeCommand(Config::$database_mongodb_name_precommit, new Command(array('create' => 'balances')));
        $this->db->m_manager->executeCommand(Config::$database_mongodb_name_precommit, new Command(array('create' => 'deposits')));
        $this->db->m_manager->executeCommand(Config::$database_mongodb_name_precommit, new Command(array('create' => 'attributes')));
        $this->db->m_manager->executeCommand(Config::$database_mongodb_name_committed, new Command(array('create' => 'blocks')));
        $this->db->m_manager->executeCommand(Config::$database_mongodb_name_committed, new Command(array('create' => 'transactions')));
        $this->db->m_manager->executeCommand(Config::$database_mongodb_name_committed, new Command(array('create' => 'balances')));
        $this->db->m_manager->executeCommand(Config::$database_mongodb_name_committed, new Command(array('create' => 'deposits')));
        $this->db->m_manager->executeCommand(Config::$database_mongodb_name_committed, new Command(array('create' => 'attributes')));
    }

    function MakeGenesisData($transactions, $attributes, $balances, $deposits, $block, $tracker) {
        # Attribute
        $bulk = new BulkWrite();
        $namespace = Config::$database_mongodb_name_precommit . '.attributes';
        foreach($attributes as $item)
            $bulk->insert($item);
        $this->db->m_manager->executeBulkWrite($namespace, $bulk);

        # Balance
        $bulk = new BulkWrite();
        $namespace = Config::$database_mongodb_name_precommit . '.balances';
        foreach($balances as $item)
            $bulk->insert($item);
        $this->db->m_manager->executeBulkWrite($namespace, $bulk);

        # Deposit
        $bulk = new BulkWrite();
        $namespace = Config::$database_mongodb_name_precommit . '.deposits';
        foreach($deposits as $item)
            $bulk->insert($item);
        $this->db->m_manager->executeBulkWrite($namespace, $bulk);

        # Transaction
        $bulk = new BulkWrite();
        $namespace = Config::$database_mongodb_name_committed . '.transactions';
        foreach($transactions as $item)
            $bulk->insert($item);
        $this->db->m_manager->executeBulkWrite($namespace, $bulk);

        # Attribute
        $bulk = new BulkWrite();
        $namespace = Config::$database_mongodb_name_committed . '.attributes';
        foreach($attributes as $item)
            $bulk->insert($item);
        $this->db->m_manager->executeBulkWrite($namespace, $bulk);

        # Balance
        $bulk = new BulkWrite();
        $namespace = Config::$database_mongodb_name_committed . '.balances';
        foreach($balances as $item)
            $bulk->insert($item);
        $this->db->m_manager->executeBulkWrite($namespace, $bulk);

        # Deposit
        $bulk = new BulkWrite();
        $namespace = Config::$database_mongodb_name_committed . '.deposits';
        foreach($deposits as $item)
            $bulk->insert($item);
        $this->db->m_manager->executeBulkWrite($namespace, $bulk);

        # Block
        $bulk = new BulkWrite();
        $namespace = Config::$database_mongodb_name_committed . '.blocks';
        $bulk->insert($block);
        $this->db->m_manager->executeBulkWrite($namespace, $bulk);

        # Tracker
        $bulk = new BulkWrite();
        $namespace = Config::$database_mongodb_name_tracker . '.validator';
        $bulk->insert($tracker);
        $this->db->m_manager->executeBulkWrite($namespace, $bulk);
    }
}