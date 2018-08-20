<?php

namespace System;

class API
{
    private $result = array();
    public $data = array();

    public function Call()
    {
        $this->_awake();
        $this->_process();
        $this->_end();

        $this->Success();
    }

    protected function Success($display_params = 'on')
    {
        $this->result['status'] = 'success';
        $this->result['data'] = $this->data;

        if ($display_params === 'on')
            $this->result['params'] = $_REQUEST;

        $this->View();
    }

    protected function Fail($code, $msg = '')
    {
        $this->result['status'] = 'fail';
        $this->result['code'] = $code;
        $this->result['msg'] = $msg;

        $this->View();
    }

    protected function View()
    {
        header("Content-Type: application/json; charset=utf-8;");
        echo json_encode($this->result);
        exit();
    }

    public function _awake() {}
    public function _process() {}
    public function _end() {}

    function Error403($msg = 'Forbidden') { $this->Fail(403, $msg); }
    function Error404($msg = 'API Not Found') { $this->Fail(404, $msg); }
    function Error($msg = 'Error', $code = 999) { $this->Fail($code, $msg); }
}