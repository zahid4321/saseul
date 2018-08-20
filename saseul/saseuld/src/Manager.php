<?php
namespace src;

class Manager {

    private $application;

    function __construct()
    {
        $this->_DefaultSetting();
        $this->_LoadClasses();

        $this->application = new Application();
    }

    function _DefaultSetting()
    {
        date_default_timezone_set('Asia/Seoul');
        session_start();
        ini_set('memory_limit','1024M');
    }

    function _LoadClasses()
    {
        spl_autoload_register(function ($class_name) {
            if (!class_exists($class_name) && preg_match("/^(src)/", $class_name)) {
                $class = str_replace("\\", "/", $class_name);
                require_once($class . ".php");
            }
        });
    }

    function Main() {
        $this->application->Main();
    }
}