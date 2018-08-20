<?php

namespace src\Util;
class RestCall
{
    function __construct($timeout = 15)
    {
        $this->timeout = $timeout;
    }

    public $timeout;
    public $RestObj;

    public function GET($url, $ssl = false, $header = array())
    {
        $this->RestObj = curl_init();

        curl_setopt($this->RestObj, CURLOPT_URL, $url);
        curl_setopt($this->RestObj, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->RestObj, CURLOPT_SSL_VERIFYPEER, $ssl);
        curl_setopt($this->RestObj, CURLOPT_TIMEOUT, $this->timeout);

        if (count($header) > 0) {
            curl_setopt($this->RestObj, CURLOPT_HTTPHEADER, $header);
        }

        $returnVal = curl_exec($this->RestObj);
        curl_close($this->RestObj);

        return $returnVal;
    }

    public function POST($url, $data = array(), $ssl = false, $header = array())
    {
        $this->RestObj = curl_init();

        curl_setopt($this->RestObj, CURLOPT_URL, $url);
        curl_setopt($this->RestObj, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->RestObj, CURLOPT_SSL_VERIFYPEER, $ssl);
        curl_setopt($this->RestObj, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($this->RestObj, CURLOPT_POST, true);
        curl_setopt($this->RestObj, CURLOPT_POSTFIELDS, $data);

        if (count($header) > 0) {
            curl_setopt($this->RestObj, CURLOPT_HTTPHEADER, $header);
        }

        $returnVal = curl_exec($this->RestObj);
        curl_close($this->RestObj);

        return $returnVal;
    }

    public function WITHCURL($curl_string)
    {
        return shell_exec($curl_string);
    }

    public function INFO()
    {
        return curl_getinfo($this->RestObj);
    }

    function DataToString($datas)
    {
        $returnStr = "";

        if (gettype($datas) == "array" && count($datas) > 0) {
            $conStr = "";

            foreach ($datas as $key => $value) {
                $returnStr .= $conStr . $key . "=" . $value;
                $conStr = "&";
            }
        }

        return $returnStr;
    }
}