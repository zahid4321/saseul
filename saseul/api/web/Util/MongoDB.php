<?php
namespace Util;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;

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

    public function Write($collection, $q_array) {
        $target = $this->db_name . '.' . $collection;
        $bulk = new BulkWrite();
        $bulk->insert($q_array);
        $this->m_manager->executeBulkWrite($target, $bulk);
    }

    public function Query($collection, $q_array) {
        $target = $this->db_name . '.' . $collection;
        $query = new Query($q_array);
        $this->m_manager->executeQuery($target, $query);
    }
}