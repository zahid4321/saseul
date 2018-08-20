<?php

namespace API\Transaction;

use Func\Key;
use System\API;
use System\Config;
use Util\Logger;

class GetBalanceTest extends API
{
    private $getbalance;
    private $key;

    function __construct()
    {
        $this->getbalance = new GetBalance();
        $this->key = new Key();
    }

    function _process()
    {
        if (!Config::$testable)
            $this->Error('Can\'t test');

        $private_key = Config::$node_private_key;
        $public_key = Config::$node_public_key;
        $address = Config::$node_address;

        $test_transaction = array(
            'version' => Config::$version,
            'type' => Config::$transaction_type_get_balance,
            'public_key' => $public_key,
            'from' => $address,
            'to' => $address,
            'timestamp' => Logger::Microtime(),
        );

        $thash = hash('sha256', json_encode($test_transaction));
        $test_signature = $this->key->MakeSignature($thash, $private_key, $public_key);
        $test_transaction['signature'] = $test_signature;

        $_REQUEST['transaction'] = json_encode($test_transaction);
        $this->getbalance->Call();
    }
}