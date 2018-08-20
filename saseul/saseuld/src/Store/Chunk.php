<?php

namespace src\Store;

use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Query;
use src\Func\Key;
use src\System\Config;
use src\System\Database;
use src\Util\Logger;
use src\Util\RestCall;

class Chunk
{
    private $db;
    private $key;
    private $rest;

    private $m_broadcast_chunk_cmd;
    private $m_get_chunk_query;
    private $m_broadcast_chunk_urls;

    function __construct()
    {
        $this->db = new Database();
        $this->key = new Key();
        $this->rest = new RestCall();

        $this->m_broadcast_chunk_cmd = new Command(array('listCollections' => 1));
        $this->m_get_chunk_query = new Query([]);
    }

    function GetBroadcastChunks() {
        $namespace = Config::$database_mongodb_name_consensus;
        $rs = $this->db->m_manager->executeCommand($namespace, $this->m_broadcast_chunk_cmd);
        $chunks = [];
        $now_id = $this->GetID();

        foreach ($rs as $item) {
            if (preg_match('/^(transactions_[0-9]+)$/', $item->name)) {
                $chunk_id = explode('_', $item->name)[1];

                if ((int)$now_id > (int)$chunk_id) {
                    $chunks[] = $item->name;
                }
            }
        }

        return $chunks;
    }

    function WriteBroadcastChunk($chunk_name) {
        $namespace = Config::$database_mongodb_name_consensus . '.' . $chunk_name;
        $rs = $this->db->m_manager->executeQuery($namespace, $this->m_get_chunk_query);

        $bulk = new BulkWrite();

        $filename = Config::$directory_broadcast_chunks . $chunk_name . '.json';

        if (file_exists($filename))
            unlink($filename);

        $file = fopen($filename, 'w');

        fwrite($file, "[\n");
        $first = true;
        foreach ($rs as $item) {
            $transaction = [];
            foreach ($item as $key => $value) {
                if ($key === '_id')
                    continue;
                $transaction[$key] = $value;
            }

            if ($first) {
                fwrite($file, json_encode($transaction));
                $first = false;
            }
            else {
                fwrite($file,",\n" . json_encode($transaction));
            }

            $transaction = $this->ProcessTransaction($transaction);
            $bulk->insert($transaction);
        }
        fwrite($file, "\n]");
        fclose($file);

        $namespace = Config::$database_mongodb_name_precommit . '.transactions';
        $this->db->m_manager->executeBulkWrite($namespace, $bulk);
        $this->m_broadcast_chunk_urls[] = 'http://' . Config::$server_host . Config::$directory_api_chunks . $chunk_name . '.json';
    }

    function WriteCollectChunk($chunk_urls) {
        foreach ($chunk_urls as $url) {
            $bulk = new BulkWrite();
            $chunks = $this->CollectChunk($url);

            foreach ($chunks as $item) {
                $transaction = [];
                foreach ($item as $key => $value) {
                    $transaction[$key] = $value;
                }

                $transaction = $this->ProcessTransaction($item);
                $bulk->insert($transaction);
            }

            $namespace = Config::$database_mongodb_name_precommit . '.transactions';
            $this->db->m_manager->executeBulkWrite($namespace, $bulk);
        }
    }

    function CollectChunk($url) {
        $rs = $this->CollectChunk($url);
        return json_decode($rs, true);
    }

    function GetChunkUrl() {
        $namespace = Config::$database_mongodb_name_consensus . '.collect_chunk_meta';
        $rs = $this->db->m_manager->executeQuery($namespace, $this->m_get_chunk_query);
        $urls = [];

        foreach ($rs as $item) {
            $urls[] = $item->url;
        }

        return $urls;
    }

    function ProcessTransaction($r_transaction) {
        $transaction = array(
            'version' => $r_transaction['version'],
            'type' => $r_transaction['type'],
            'public_key' => $r_transaction['public_key'],
            'from' => $r_transaction['from'],
            'to' => $r_transaction['to'],
            'value' => $r_transaction['value'],
            'fee' => $r_transaction['fee'],
            'timestamp' => $r_transaction['timestamp'],
        );

        $transaction['thash'] = hash('sha256', json_encode($transaction));
        $transaction['signature'] = $r_transaction['signature'];

        $transaction['result'] = '';
        $transaction['status'] = 'broadcast';
        $transaction['block'] = '';

        return $transaction;
    }

    function DropChunkCollection($chunk_name) {
        $namespace = Config::$database_mongodb_name_consensus;
        $command = new Command(array('drop' => $chunk_name));
        $this->db->m_manager->executeCommand($namespace, $command);
    }

    function WriteBroadcastChunks($chunks) {
        $this->m_broadcast_chunk_urls = [];

        foreach ($chunks as $chunk) {
            $this->WriteBroadcastChunk($chunk);
            $this->DropChunkCollection($chunk);
        }

        return $this->m_broadcast_chunk_urls;
    }

    public function GetID() {
        $timestamp = Logger::Microtime();
        $tid = $timestamp - ($timestamp % Config::$microinterval_chunk);
        $cid = preg_replace('/0{6}$/', '', $tid);
        return $cid;
    }
}