<?php

namespace API\Validator;

use Func\TransactionInterface;
use Store\Tracker;
use System\API;
use System\Config;

class TrackerRequest extends API
{
    private $transaction_interface;
    private $tracker;
    private $r_transaction;

    function __construct()
    {
        $this->transaction_interface = new TransactionInterface();
        $this->tracker = new Tracker();
    }

    function _awake()
    {
        $this->r_transaction = json_decode($_REQUEST['transaction'], true);
    }

    function _process()
    {
        $this->ValidateTransaction();
        $this->AddTracker();
    }

    function ValidateTransaction()
    {
        $validate = $this->transaction_interface->ValidateBroadcast($this->r_transaction, Config::$v_transaction_type_addtracker);

        if ($validate !== true)
            $this->Error($validate['msg'], $validate['code']);
    }

    function AddTracker() {
        $this->tracker->AddTracker($this->r_transaction);
    }
}