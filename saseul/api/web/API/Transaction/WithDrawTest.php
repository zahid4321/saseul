<?php

namespace API\Transaction;

use Func\Key;
use System\API;
use System\Config;
use Util\Logger;

class WithDrawTest extends API
{
    private $withdraw;
    private $key;

    function __construct()
    {
        $this->withdraw = new WithDraw();
        $this->key = new Key();
    }

    function _process()
    {
        if (!Config::$testable)
            $this->Error('Can\'t test');

        $this->WithDraw();
    }

    function WithDraw() {
        $private_key = Config::$node_private_key;
        $public_key = Config::$node_public_key;
        $address = Config::$node_address;

        $withdraw_value = 26 * 100 * 10000 * 10000;

        $test_transaction = array(
            'version' => Config::$version,
            'type' => Config::$transaction_type_withdraw,
            'public_key' => $public_key,
            'from' => $address,
            'to' => $address,
            'value' => $withdraw_value,
            'fee' => 0,
            'timestamp' => Logger::Microtime(),
        );

        $thash = hash('sha256', json_encode($test_transaction));
        $test_signature = $this->key->MakeSignature($thash, $private_key, $public_key);
        $test_transaction['signature'] = $test_signature;

        $_REQUEST['transaction'] = json_encode($test_transaction);
        $this->withdraw->_awake();
        $this->withdraw->_process();
        $this->withdraw->_end();
    }
}