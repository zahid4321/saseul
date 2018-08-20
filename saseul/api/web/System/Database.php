<?php
namespace System;

use Util\MongoDB;

class Database extends MongoDB
{
    function Init()
    {
        $this->db_host = Config::$database_mongodb_host;
    }
}