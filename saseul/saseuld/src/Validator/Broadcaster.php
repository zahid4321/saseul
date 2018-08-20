<?php
namespace src\Validator;

use MongoDB\Driver\Query;
use src\Func\Key;
use src\System\Config;
use src\System\Database;
use src\Util\Logger;
use src\Util\RestCall;

class Broadcaster {

    private $db;
    private $rest;
    private $key;

    private $m_query;
    private $m_validator;
    private $m_supervisor;

    private $m_v_transaction;

    function __construct()
    {
        $this->db = new Database();
        $this->rest = new RestCall(Config::$broadcast_timeout);
        $this->key = new Key();

        $this->m_query = new Query([]);
    }

    function Broadcast() {
        $data = ['transaction' => json_encode($this->m_v_transaction)];

        foreach ($this->m_supervisor as $v) {
            $url = 'http://' . $v . Config::$broadcast_request_url;
            $this->rest->POST($url, $data);
        }

        foreach ($this->m_validator as $v) {
            $url = 'http://' . $v . Config::$broadcast_request_url;
            $this->rest->POST($url, $data);
        }
    }

    function BroadcastChunk($chunk_urls) {
        $this->GetHosts();
        $this->MakeBroadcastTransaction($chunk_urls);
        $this->Broadcast();
    }

    function MakeBroadcastTransaction($chunk_urls) {
        $this->m_v_transaction = array(
            'version' => Config::$version,
            'type' => Config::$v_transaction_type_chunk_broadcast,
            'public_key' => Config::$node_public_key,
            'from' => Config::$node_address,
            'to' => Config::$node_address,
            'timestamp' => Logger::Microtime(),
            'value' => $chunk_urls,
        );

        $thash = hash('sha256', json_encode($this->m_v_transaction));
        $signature = $this->key->MakeSignature($thash, Config::$node_private_key, Config::$node_public_key);
        $this->m_v_transaction['signature'] = $signature;
    }

    function GetHosts() {
        $this->m_validator = [];
        $this->m_supervisor = [];

        $namespace = Config::$database_mongodb_name_tracker . '.validator';
        $rs = $this->db->m_manager->executeQuery($namespace, $this->m_query);

        foreach ($rs as $item) {
            if ($item->address !== Config::$node_address)
                $this->m_validator[$item->address] = $item->host;
        }

        $namespace = Config::$database_mongodb_name_tracker . '.supervisor';
        $rs = $this->db->m_manager->executeQuery($namespace, $this->m_query);

        foreach ($rs as $item) {
            if ($item->address !== Config::$node_address)
                $this->m_supervisor[$item->address] = $item->host;
        }
    }
}