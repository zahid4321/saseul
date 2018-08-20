<?php
namespace src\Communication;

use src\Util\RestCall;

class Broadcaster {

    private $rest;

    function __construct()
    {
        $this->rest = new RestCall();
    }

    function BroadcastChunkUrls($chunk_urls) {
        
    }
}