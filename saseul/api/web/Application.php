<?php

class Application
{
    public $allowed_ip = [];
    public $ip_authentication = false;

    private $allowed_directory = [];
    private $handler;

    private $route = 200;

    function __construct()
    {
        $this->_DefaultSetting();
        $this->_CustomSetting();

        $this->_Authentication();
        $this->_LoadClasses();

        $this->_Route();
    }

    function _DefaultSetting()
    {
        date_default_timezone_set('Asia/Seoul');
        session_start();
    }

    function _CustomSetting()
    {
        $this->allowed_ip[] = '1.*.*.*';
        $this->ip_authentication = false;

//        $this->allowed_directory[] = 'API';
//        $this->allowed_directory[] = 'System';
//        $this->allowed_directory[] = 'Util';
//        $this->allowed_directory[] = 'Func';
//        $this->allowed_directory[] = 'Store';
    }

    function _Authentication()
    {
        if ($this->ip_authentication === false)
            return;

        $sign = false;

        foreach ($this->allowed_ip as $ip) {
            $ip_pattern = str_replace('.', "\\.", $ip);
            $ip_pattern = str_replace('*', '[0-9]+', $ip_pattern);

            if (preg_match("/^({$ip_pattern})$/i", $_SERVER['REMOTE_ADDR'])) {
                $sign = true;
            }
        }

        if ($sign === false)
            $this->route = 403;
    }

    function _LoadClasses()
    {
        spl_autoload_register(function ($class_name) {
//            $directory = explode('\\', $class_name);

            if (!class_exists($class_name)) {
//                if (in_array($directory[0], $this->allowed_directory) === true) {
                $class = str_replace("\\", "/", $class_name);
                require_once($class . ".php");
//                }
            }
        });
    }

    function _Route()
    {
        $this->handler = 'main';

        if (isset($_REQUEST['handler']))
            $this->handler = $_REQUEST['handler'];

        $dir = $this->find_real_dir("API/{$this->handler}.php");

        if ($this->route === 200 && $dir === false)
            $this->route = 404;

        $dir = substr($dir, 4, mb_strlen($dir) - 8);

        $class = "API\\{$dir}";
        $class = preg_replace("/\\/{2,}/", "/", $class);
        $class = str_replace("/", "\\", $class);

        if ($this->route === 200) {
            $api = new $class();
        } else {
            $api = new \System\API();
        }

        switch ($this->route) {
            case 403:
                $api->Error403();
                break;
            case 404:
                $api->Error404();
                break;
            case 200:
                $api->Call();
                break;
            default:
                $api->Error();
                break;
        }
    }

    private function find_real_dir($full_dir)
    {
        $full_dir = "./" . $full_dir;
        $dir = preg_replace("/\\/{2,}/", "/", $full_dir);
        $dir = explode("/", $dir);

        if (count($dir) > 1) {
            $parent = $dir[0];

            for ($i = 1; $i < count($dir); $i++) {
                $find = $dir[$i];

                if ($this->find_file($parent, $find) === false) {
                    return false;
                    break;
                } else {
                    $parent = $this->find_file($parent, $find);
                    if (strtolower($parent) == strtolower($full_dir)) {
                        $parent = substr($parent, 2);
                        return $parent;
                    }
                }
            }
        }

        return false;
    }

    private function find_file($parent_dir, $search_str)
    {
        if (file_exists($parent_dir)) {
            $d = scandir($parent_dir);
            $lower_d = false;
            for ($i = 0; $i < count($d); $i++) {
                if (strtolower($search_str) === strtolower($d[$i])) {
                    $lower_d = $d[$i];
                    break;
                }
            }
            return $parent_dir . "/" . $lower_d;
        }

        return false;
    }
}