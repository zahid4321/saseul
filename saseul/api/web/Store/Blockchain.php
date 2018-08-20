<?php

namespace Store;

use MongoDB\Driver\Query;
use System\Config;
use System\Database;

class Blockchain
{
    private $db;

    function __construct()
    {
        $this->db = new Database();
    }

    function CheckBalance($address) {
        $query = new Query(array('address' => $address));
        $namespace = Config::$database_mongodb_name_precommit . '.balances';
        $rs = $this->db->m_manager->executeQuery($namespace, $query);
        $precommit_value = 0;

        foreach ($rs as $item) {
            $precommit_value = $item->value;
            break;
        }

        $query = new Query(array('address' => $address));
        $namespace = Config::$database_mongodb_name_committed . '.balances';
        $rs = $this->db->m_manager->executeQuery($namespace, $query);
        $committed_value = 0;

        foreach ($rs as $item) {
            $committed_value = $item->value;
            break;
        }

        return array(
            'precommit' => $precommit_value,
            'committed' => $committed_value,
        );
    }

    function CheckDeposit($address) {
        $query = new Query(array('address' => $address));
        $namespace = Config::$database_mongodb_name_precommit . '.deposits';
        $rs = $this->db->m_manager->executeQuery($namespace, $query);
        $precommit_value = 0;

        foreach ($rs as $item) {
            $precommit_value = $item->value;
            break;
        }

        $query = new Query(array('address' => $address));
        $namespace = Config::$database_mongodb_name_committed . '.deposits';
        $rs = $this->db->m_manager->executeQuery($namespace, $query);
        $committed_value = 0;

        foreach ($rs as $item) {
            $committed_value = $item->value;
            break;
        }

        return array(
            'precommit' => $precommit_value,
            'committed' => $committed_value,
        );
    }
}