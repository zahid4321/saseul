<?php
namespace API\Request;

use Func\Key;
use System\API;

class CreateAddress extends API {

    private $key;

    function __construct()
    {
        $this->key = new Key();
    }

    function _process()
    {
        $this->data['private_key'] = $this->key->MakePrivateKey();
        $this->data['public_key'] = $this->key->MakePublicKey($this->data['private_key']);
        $this->data['address'] = $this->key->MakeAddress($this->data['public_key']);
    }
}