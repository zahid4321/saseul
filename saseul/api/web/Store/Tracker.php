<?php

namespace Store;

use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Query;
use System\Config;
use System\Database;
use Util\Logger;

class Tracker
{
    private $db;

    function __construct()
    {
        $this->db = new Database();
    }

    function GetValidator() {
        $query = new Query([]);
        $namespace = Config::$database_mongodb_name_tracker . '.validator';
        $rs = $this->db->m_manager->executeQuery($namespace, $query);
        $validator = [];

        foreach ($rs as $item) {
            if ($item !== Config::$node_address)
                $validator[] = $item;
        }

        return $validator;
    }

    function GetFullNodes() {
        $query = new Query([]);
        $namespace = Config::$database_mongodb_name_committed . '.attributes';
        $rs = $this->db->m_manager->executeQuery($namespace, $query);
        $fullnodes = [];

        foreach ($rs as $item) {
            $fullnodes[$item->address] = $item->value;
        }

        return $fullnodes;
    }

    function AddTracker($v_transaction) {
        $fullnodes = $this->GetFullNodes();

        if (isset($fullnodes[$v_transaction['from']])) {
            $bulk = new BulkWrite();
            $bulk->insert(['host' => $v_transaction['value'], 'address' => $v_transaction['from']]);
            $namespace = Config::$database_mongodb_name_tracker . '.' . $fullnodes[$v_transaction['from']];
            $this->db->m_manager->executeBulkWrite($namespace, $bulk);
        }
    }
}