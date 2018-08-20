<?php

namespace Func;

use System\Config;
use Util\Logger;

class TransactionInterface
{
    private $key;
    private $m_timestamp;

    function __construct()
    {
        $this->key = new Key();
        $this->m_timestamp = Logger::Microtime();
    }

    function ValidateBroadcast($r_transaction, $type) {
        if (!isset($r_transaction['version'])) return $this->Error('Invalid transaction', 900);
        if (!isset($r_transaction['type'])) return $this->Error('Invalid transaction', 900);
        if (!isset($r_transaction['public_key'])) return $this->Error('Invalid transaction', 900);
        if (!isset($r_transaction['from'])) return $this->Error('Invalid transaction', 900);
        if (!isset($r_transaction['to'])) return $this->Error('Invalid transaction', 900);
        if (!isset($r_transaction['value'])) return $this->Error('Invalid transaction', 900);
        if (!isset($r_transaction['timestamp'])) return $this->Error('Invalid transaction', 900);
        if (!isset($r_transaction['signature'])) return $this->Error('Invalid transaction', 900);

        if ($r_transaction['version'] !== Config::$version)
            return $this->Error('Invalid version', 900);

        if ($r_transaction['type'] !== $type)
            return $this->Error('Invalid type', 900);

        if (preg_match('/^[0-9a-f]{64}$/', $r_transaction['public_key']) != true)
            return $this->Error('Invalid public_key', 900);

        if (preg_match('/^[0-9a-fx]{48}$/', $r_transaction['from']) != true)
            return $this->Error('Invalid from address', 900);

        if ($this->key->MakeAddress($r_transaction['public_key']) !== $r_transaction['from'])
            return $this->Error('Invalid from address', 900);

        if (preg_match('/^[0-9a-fx]{48}$/', $r_transaction['to']) != true)
            return $this->Error('Invalid to address', 900);

        if (is_numeric($r_transaction['timestamp']) != true)
            return $this->Error('Invalid timestamp', 900);

        if ((int)$r_transaction['timestamp'] + Config::$microinterval_validtransaction < $this->m_timestamp)
            return $this->Error('Invalid timestamp', 900);

        $transaction = array(
            'version' => $r_transaction['version'],
            'type' => $r_transaction['type'],
            'public_key' => $r_transaction['public_key'],
            'from' => $r_transaction['from'],
            'to' => $r_transaction['to'],
            'timestamp' => $r_transaction['timestamp'],
            'value' => $r_transaction['value'],
        );

        $thash = hash('sha256', json_encode($transaction));

        if ($this->key->ValidSignature($thash, $r_transaction['public_key'], $r_transaction['signature']) === false)
            return $this->Error('Invalid signature', 900);

        return true;
    }

    function ValidateGet($r_transaction, $type) {
        if (!isset($r_transaction['version'])) return $this->Error('Invalid transaction', 900);
        if (!isset($r_transaction['type'])) return $this->Error('Invalid transaction', 900);
        if (!isset($r_transaction['public_key'])) return $this->Error('Invalid transaction', 900);
        if (!isset($r_transaction['from'])) return $this->Error('Invalid transaction', 900);
        if (!isset($r_transaction['to'])) return $this->Error('Invalid transaction', 900);
        if (!isset($r_transaction['timestamp'])) return $this->Error('Invalid transaction', 900);
        if (!isset($r_transaction['signature'])) return $this->Error('Invalid transaction', 900);

        if ($r_transaction['version'] !== Config::$version)
            return $this->Error('Invalid version', 900);

        if ($r_transaction['type'] !== $type)
            return $this->Error('Invalid type', 900);

        if (preg_match('/^[0-9a-f]{64}$/', $r_transaction['public_key']) != true)
            return $this->Error('Invalid public_key', 900);

        if (preg_match('/^[0-9a-fx]{48}$/', $r_transaction['from']) != true)
            return $this->Error('Invalid from address', 900);

        if ($this->key->MakeAddress($r_transaction['public_key']) !== $r_transaction['from'])
            return $this->Error('Invalid from address', 900);

        if (preg_match('/^[0-9a-fx]{48}$/', $r_transaction['to']) != true)
            return $this->Error('Invalid to address', 900);

        if (is_numeric($r_transaction['timestamp']) != true)
            return $this->Error('Invalid timestamp', 900);

        if ((int)$r_transaction['timestamp'] + Config::$microinterval_validtransaction < $this->m_timestamp)
            return $this->Error('Invalid timestamp', 900);

        $transaction = array(
            'version' => $r_transaction['version'],
            'type' => $r_transaction['type'],
            'public_key' => $r_transaction['public_key'],
            'from' => $r_transaction['from'],
            'to' => $r_transaction['to'],
            'timestamp' => $r_transaction['timestamp'],
        );

        $thash = hash('sha256', json_encode($transaction));

        if ($this->key->ValidSignature($thash, $r_transaction['public_key'], $r_transaction['signature']) === false)
            return $this->Error('Invalid signature', 900);

        return true;
    }

    function ValidateSend($r_transaction, $type) {
        if (!isset($r_transaction['version'])) return $this->Error('Invalid transaction', 900);
        if (!isset($r_transaction['type'])) return $this->Error('Invalid transaction', 900);
        if (!isset($r_transaction['public_key'])) return $this->Error('Invalid transaction', 900);
        if (!isset($r_transaction['from'])) return $this->Error('Invalid transaction', 900);
        if (!isset($r_transaction['to'])) return $this->Error('Invalid transaction', 900);
        if (!isset($r_transaction['value'])) return $this->Error('Invalid transaction', 900);
        if (!isset($r_transaction['fee'])) return $this->Error('Invalid transaction', 900);
        if (!isset($r_transaction['timestamp'])) return $this->Error('Invalid transaction', 900);
        if (!isset($r_transaction['signature'])) return $this->Error('Invalid transaction', 900);

        if ($r_transaction['version'] !== Config::$version)
            return $this->Error('Invalid version', 900);

        if ($r_transaction['type'] !== $type)
            return $this->Error('Invalid type', 900);

        if (preg_match('/^[0-9a-f]{64}$/', $r_transaction['public_key']) != true)
            return $this->Error('Invalid public_key', 900);

        if (preg_match('/^[0-9a-fx]{48}$/', $r_transaction['from']) != true)
            return $this->Error('Invalid from address', 900);

        if ($this->key->MakeAddress($r_transaction['public_key']) !== $r_transaction['from'])
            return $this->Error('Invalid from address', 900);

        if (preg_match('/^[0-9a-fx]{48}$/', $r_transaction['to']) != true)
            return $this->Error('Invalid to address', 900);

        if (is_numeric($r_transaction['value']) != true)
            return $this->Error('Invalid value', 900);

        if (is_numeric($r_transaction['fee']) != true)
            return $this->Error('Invalid fee', 900);

        if (is_numeric($r_transaction['timestamp']) != true)
            return $this->Error('Invalid timestamp', 900);

        if ((int)$r_transaction['timestamp'] + Config::$microinterval_validtransaction < $this->m_timestamp)
            return $this->Error('Invalid timestamp', 900);

        $transaction = array(
            'version' => $r_transaction['version'],
            'type' => $r_transaction['type'],
            'public_key' => $r_transaction['public_key'],
            'from' => $r_transaction['from'],
            'to' => $r_transaction['to'],
            'value' => $r_transaction['value'],
            'fee' => $r_transaction['fee'],
            'timestamp' => $r_transaction['timestamp'],
        );

        $thash = hash('sha256', json_encode($transaction));

        if ($this->key->ValidSignature($thash, $r_transaction['public_key'], $r_transaction['signature']) === false)
            return $this->Error('Invalid signature', 900);

        return true;
    }

    function Error($msg, $code) {
        return array(
            'msg' => $msg,
            'code' => $code,
        );
    }
}