<?php

namespace API\Util;

use System\API;
use System\Config;
use Util\RestCall;

class RestCallTest extends API
{
    private $rest;

    function __construct()
    {
        $this->rest = new RestCall();
    }

    function _process()
    {
        if (!Config::$testable)
            $this->Error('Can\'t test');
        $this->data = $this->GetData();
    }

    function GetData() {
        return $this->rest->GET("http://{$_SERVER['HTTP_HOST']}", false);
    }
}