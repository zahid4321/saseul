<?php

namespace API\Transaction;

use Func\TransactionInterface;
use Store\Blockchain;
use System\API;
use System\Config;

class GetBalance extends API
{
    private $transaction_interface;
    private $blockchain;
    private $r_transaction;

    function __construct()
    {
        $this->transaction_interface = new TransactionInterface();
        $this->blockchain = new Blockchain();
    }

    function _awake()
    {
        $this->r_transaction = json_decode($_REQUEST['transaction'], true);
    }

    function _process()
    {
        $this->ValidateTransaction();
        $this->CheckBalance();
    }

    function ValidateTransaction() {
        $validate = $this->transaction_interface->ValidateGet($this->r_transaction, Config::$transaction_type_get_balance);

        if ($validate !== true)
            $this->Error($validate['msg'], $validate['code']);
    }

    function CheckBalance() {
        $this->data['address'] = $this->r_transaction['from'];
        $this->data['balance'] = $this->blockchain->CheckBalance($this->r_transaction['from']);
        $this->data['deposit'] = $this->blockchain->CheckDeposit($this->r_transaction['from']);
    }
}