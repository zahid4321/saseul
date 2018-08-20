<?php

namespace API\Util;

use System\API;
use System\Config;
use Util\Ed25519;

class Ed25519Test extends API
{
    private $ed25519;

    function __construct()
    {
        $this->ed25519 = new Ed25519();
    }

    function _process()
    {
        if (!Config::$testable)
            $this->Error('Can\'t test');

        $message = 'util test';
        $this->data['private_key'] = $this->ed25519->MakePrivateKey();
        $this->data['public_key'] = $this->ed25519->MakePublicKey($this->data['private_key']);
        $this->data['signature'] = $this->ed25519->MakeSignature($message, $this->data['private_key'], $this->data['public_key']);
        $this->data['validation'] = $this->ed25519->ValidSignature($message, $this->data['public_key'], $this->data['signature']);
    }
}