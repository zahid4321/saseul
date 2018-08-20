<?php
namespace Func;

use System\Config;
use Util\Ed25519;

class Key {

    private $util;

    function __construct()
    {
        switch (Config::$key_algorithm) {
            case 'Ed25519':
                $this->util = new Ed25519();
                break;
            default:
                $this->util = new Ed25519();
                break;
        }
    }

    function MakePrivateKey() {
        return $this->util->MakePrivateKey();
    }

    function MakePublicKey($private_key) {
        return $this->util->MakePublicKey($private_key);
    }

    function MakeAddress($public_key) {
        return $this->util->MakeAddress($public_key, Config::$address_prefix_0, Config::$address_prefix_1);
    }

    function MakeSignature($str, $private_key, $public_key) {
        return $this->util->MakeSignature($str, $private_key, $public_key);
    }

    function ValidSignature($str, $public_key, $signature) {
        return $this->util->ValidSignature($str, $public_key, $signature);
    }
}