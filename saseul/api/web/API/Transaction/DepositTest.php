<?php

namespace API\Transaction;

use Func\Key;
use System\API;
use System\Config;
use Util\Logger;

class DepositTest extends API
{
    private $deposit;
    private $key;

    function __construct()
    {
        $this->deposit = new Deposit();
        $this->key = new Key();
    }

    function _process()
    {
        if (!Config::$testable)
            $this->Error('Can\'t test');

        $this->Deposit();
    }

    function Deposit() {
        $private_key = Config::$node_private_key;
        $public_key = Config::$node_public_key;
        $address = Config::$node_address;

        $deposit_value = 35 * 100 * 10000 * 10000;

        $test_transaction = array(
            'version' => Config::$version,
            'type' => Config::$transaction_type_deposit,
            'public_key' => $public_key,
            'from' => $address,
            'to' => $address,
            'value' => $deposit_value,
            'fee' => 0,
            'timestamp' => Logger::Microtime(),
        );

        $thash = hash('sha256', json_encode($test_transaction));
        $test_signature = $this->key->MakeSignature($thash, $private_key, $public_key);
        $test_transaction['signature'] = $test_signature;

        $_REQUEST['transaction'] = json_encode($test_transaction);
        $this->deposit->_awake();
        $this->deposit->_process();
        $this->deposit->_end();
    }
}