<?php

namespace API\Validator;

use Func\TransactionInterface;
use Store\Chunk;
use Store\Tracker;
use System\API;
use System\Config;

class BroadcastRequest extends API
{
    private $transaction_interface;
    private $chunk;
    private $tracker;

    private $r_transaction;
    private $m_chunk_meta;

    function __construct()
    {
        $this->transaction_interface = new TransactionInterface();
        $this->chunk = new Chunk();
        $this->tracker = new Tracker();
    }

    function _awake()
    {
        $this->r_transaction = json_decode($_REQUEST['transaction'], true);
    }

    function _process()
    {
        # Validate fullnode
        $this->ValidateTransaction();
        $this->ValidateAddress();
        $this->MakeChunkMeta();
        $this->AddCollectChunk();
    }

    function ValidateTransaction()
    {
        $validate = $this->transaction_interface->ValidateBroadcast($this->r_transaction, Config::$v_transaction_type_chunk_broadcast);

        if ($validate !== true)
            $this->Error($validate['msg'], $validate['code']);
    }

    function ValidateAddress() {
        if (Config::$node_address === $this->r_transaction['from'])
            $this->Error('The address is yours', 900);
        
        $full_nodes = $this->tracker->GetFullNodes();

        if (!isset($full_nodes[$this->r_transaction['from']]))
            $this->Error('Invalid address', 900);
    }

    function MakeChunkMeta()
    {
        $this->m_chunk_meta = [];

        foreach ($this->r_transaction['value'] as $url) {
            $this->m_chunk_meta[] = ['url' => $url];
        }
    }

    function AddCollectChunk() {
        $this->chunk->AddCollectChunk($this->m_chunk_meta);
    }
}