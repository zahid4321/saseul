<?php
namespace src\Util;
use MongoDB\Driver\Manager;

class MongoDB
{
    protected $db_host;
    protected $db_name;

    public $m_manager;

    public function __construct()
    {
        $this->Init();

        $this->m_manager = new Manager("mongodb://{$this->db_host}");
    }

    public function Init() {
        # host, user, password, name
    }
}