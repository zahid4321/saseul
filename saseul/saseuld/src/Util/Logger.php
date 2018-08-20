<?php

namespace src\Util;
class Logger
{
    public static function Log($obj, $option = true)
    {
        print_r($obj);
        if ($option)
            exit();
    }

    public static function EchoLog($str)
    {
        echo "{$str} <br /> "; echo str_pad('', 4096);
        ob_flush();
        flush();
    }

    public static function Date()
    {
        return Date('YmdHis');
    }

    public static function Microtime()
    {
        return intval(array_sum(explode(' ', microtime())) * 1000000);
    }

    public static function MicrotimeWithComma()
    {
        return array_sum(explode(' ', microtime()));
    }

    public static function Millitime() {
        return intval(array_sum(explode(' ', microtime())) * 1000);
    }
}