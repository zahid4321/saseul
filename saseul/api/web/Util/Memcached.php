<?php
namespace Util;
class Memcached {
    public $mem;

    public function __construct()
    {
        $this->mem = new \Memcached();
        $this->mem->addServer("localhost", 11211) or die ("There is no memcached server ");
    }

    public function get($key) {
        return $this->mem->get($_SERVER['SV_PREFIX']. "_" . $key);
    }

    public function delete($key) {
        return $this->mem->delete($_SERVER['SV_PREFIX']. "_" . $key);
    }

    public function stats() {
        return $this->mem->getStats();
    }

    public function set($key, $value, $time = 0) {
        $this->mem->set($_SERVER['SV_PREFIX']. "_" . $key, $value, $time);
        return $value;
    }

    public function flush() {
        $this->mem->flush();
    }
}
