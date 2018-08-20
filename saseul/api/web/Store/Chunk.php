<?php

namespace Store;

use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Query;
use System\Config;
use System\Database;
use Util\Logger;

class Chunk
{
    private $db;

    function __construct()
    {
        $this->db = new Database();
    }

    function AddTransaction($transaction) {
        $bulk = new BulkWrite();
        $bulk->insert($transaction);
        $namespace = Config::$database_mongodb_name_consensus . '.transactions_' . $this->GetID();
        $this->db->m_manager->executeBulkWrite($namespace, $bulk);
    }

    function GetID() {
        $timestamp = Logger::Microtime();
        $tid = $timestamp - ($timestamp % Config::$microinterval_chunk);
        $cid = preg_replace('/0{6}$/', '', $tid);
        return $cid;
    }

    function AddCollectChunk($chunk_meta) {
        $bulk = new BulkWrite();

        foreach ($chunk_meta as $meta) {
            print_r($meta);
            $bulk->insert($meta);
        }

        $namespace = Config::$database_mongodb_name_consensus . '.collect_chunk_meta';
        $this->db->m_manager->executeBulkWrite($namespace, $bulk);
    }
}