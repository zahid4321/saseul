<?php
namespace API\Func;

use Func\Key;
use System\API;
use System\Config;

class KeyTest extends API {

    private $key;

    function __construct()
    {
        $this->key = new Key();
    }

    function _process()
    {
        if (!Config::$testable)
            $this->Error('Can\'t test');

        $message = 'key test';
        $this->data['private_key'] = $this->key->MakePrivateKey();
        $this->data['public_key'] = $this->key->MakePublicKey($this->data['private_key']);
        $this->data['address'] = $this->key->MakeAddress($this->data['public_key']);
        $this->data['signature'] = $this->key->MakeSignature($message, $this->data['private_key'], $this->data['public_key']);
        $this->data['validation'] = $this->key->ValidSignature($message, $this->data['public_key'], $this->data['signature']);
    }
}