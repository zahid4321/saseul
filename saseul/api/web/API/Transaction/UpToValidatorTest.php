<?php

namespace API\Transaction;

use Func\Key;
use System\API;
use System\Config;
use Util\Logger;

class UpToValidatorTest extends API
{
    private $uptovalidator;
    private $key;

    function __construct()
    {
        $this->uptovalidator = new UpToValidator();
        $this->key = new Key();
    }

    function _process()
    {
        if (!Config::$testable)
            $this->Error('Can\'t test');

        $this->UpToValidator();
    }

    function UpToValidator() {
        $private_key = Config::$testnode_private_key;
        $public_key = Config::$testnode_public_key;
        $address = Config::$testnode_address;

        $test_transaction = array(
            'version' => Config::$version,
            'type' => Config::$transaction_type_uptovalidator,
            'public_key' => $public_key,
            'from' => $address,
            'to' => $address,
            'value' => 0,
            'fee' => 0,
            'timestamp' => Logger::Microtime(),
        );

        $thash = hash('sha256', json_encode($test_transaction));
        $test_signature = $this->key->MakeSignature($thash, $private_key, $public_key);
        $test_transaction['signature'] = $test_signature;

        $_REQUEST['transaction'] = json_encode($test_transaction);
        $this->uptovalidator->_awake();
        $this->uptovalidator->_process();
        $this->uptovalidator->_end();
    }
}