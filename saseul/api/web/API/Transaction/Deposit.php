<?php

namespace API\Transaction;

use Func\TransactionInterface;
use Store\Chunk;
use System\API;
use System\Config;

class Deposit extends API
{
    private $transaction_interface;
    private $r_transaction;

    function __construct()
    {
        $this->transaction_interface = new TransactionInterface();
    }

    function _awake()
    {
        $this->r_transaction = json_decode($_REQUEST['transaction'], true);
    }

    function _process()
    {
        $this->ValidateTransaction();
        $this->AddTransaction();
    }

    function ValidateTransaction() {
        $validate = $this->transaction_interface->ValidateSend($this->r_transaction, Config::$transaction_type_deposit);

        if ($validate !== true)
            $this->Error($validate['msg'], $validate['code']);
    }

    function AddTransaction() {
        $chunk = new Chunk();
        $chunk->AddTransaction($this->r_transaction);
    }
}