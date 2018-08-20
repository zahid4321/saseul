<?php

namespace API\Validator;

use Func\Key;
use System\API;
use System\Config;
use Util\Logger;

class TrackerRequestTest extends API
{
    private $tracker_request;
    private $key;

    function __construct()
    {
        $this->tracker_request = new TrackerRequest();
        $this->key = new Key();
    }

    function _process()
    {
        if (!Config::$testable)
            $this->Error('Can\'t test');

        $this->AddTracker();
    }

    function AddTracker() {
        $private_key = Config::$testnode_private_key;
        $public_key = Config::$testnode_public_key;
        $address = Config::$testnode_address;
        $host = '';

        $test_transaction = array(
            'version' => Config::$version,
            'type' => Config::$v_transaction_type_addtracker,
            'public_key' => $public_key,
            'from' => $address,
            'to' => $address,
            'timestamp' => Logger::Microtime(),
            'value' => $host,
        );

        $thash = hash('sha256', json_encode($test_transaction));
        $test_signature = $this->key->MakeSignature($thash, $private_key, $public_key);
        $test_transaction['signature'] = $test_signature;

        $_REQUEST['transaction'] = json_encode($test_transaction);
        $this->tracker_request->_awake();
        $this->tracker_request->_process();
        $this->tracker_request->_end();
    }
}