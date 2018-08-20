<?php
namespace src\System;

use src\Util\MongoDB;

class Database extends MongoDB
{
    function Init()
    {
        $this->db_host = Config::$database_mongodb_host;
    }
}