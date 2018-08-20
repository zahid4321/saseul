<?php

namespace API\Transaction;

use Func\Key;
use System\API;
use System\Config;
use Util\Logger;

class SendCoinTest extends API
{
    private $sendcoin;
    private $key;

    private $m_keys;

    function __construct()
    {
        $this->sendcoin = new SendCoin();
        $this->key = new Key();
    }

    function _process()
    {
        if (!Config::$testable)
            $this->Error('Can\'t test');

        $this->GetKeys();

        foreach ($this->m_keys as $key) {
            $this->SendCoin($key['address']);
        }
    }

    function GetKeys() {
        $filename = $_SERVER['DOCUMENT_ROOT'] . '/Files/TestKeys/keyfile_1534716743641835.json';
        $file = fopen($filename, 'r');
        $json = fread($file, filesize($filename));
        fclose($file);

        $this->m_keys = json_decode($json, true);
    }

    function SendCoin($to) {
        $private_key = Config::$node_private_key;
        $public_key = Config::$node_public_key;
        $address = Config::$node_address;

        $send_value = rand(2000, 500000);

        $test_transaction = array(
            'version' => Config::$version,
            'type' => Config::$transaction_type_send_coin,
            'public_key' => $public_key,
            'from' => $address,
            'to' => $to,
            'value' => $send_value,
            'fee' => (int)($send_value * Config::$fee_rate),
            'timestamp' => Logger::Microtime(),
        );

        $thash = hash('sha256', json_encode($test_transaction));
        $test_signature = $this->key->MakeSignature($thash, $private_key, $public_key);
        $test_transaction['signature'] = $test_signature;

        $_REQUEST['transaction'] = json_encode($test_transaction);
        $this->sendcoin->_awake();
        $this->sendcoin->_process();
        $this->sendcoin->_end();
    }
}