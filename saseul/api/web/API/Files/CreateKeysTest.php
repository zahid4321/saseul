<?php
namespace API\Files;

use Func\Key;
use System\API;
use System\Config;
use Util\Logger;

class CreateKeysTest extends API {
    private $key;

    private $m_keys;
    private $m_files;
    private $m_make_count;

    function __construct()
    {
        $this->key = new Key();
        $this->m_make_count = 10000;
    }

    function _process()
    {
        if (!Config::$testable)
            $this->Error('Can\'t test');

        $this->m_keys = [];

        for ($i = 1; $i <= $this->m_make_count; $i++) {
            $this->MakeExampleAddress();

            if ($i % 5000 == 0) {
                $this->WriteExampleAddress();
                $this->m_keys = [];
            }
        }

        if (count($this->m_keys) > 0) {
            $this->WriteExampleAddress();
        }

        $this->data = array(
            'json_files' => $this->m_files,
        );
    }

    function MakeExampleAddress() {
        $key = [];
        $key['private_key'] = $this->key->MakePrivateKey();
        $key['public_key'] = $this->key->MakePublicKey($key['private_key']);
        $key['address'] = $this->key->MakeAddress($key['public_key']);
        $this->m_keys[] = $key;
    }

    function WriteExampleAddress() {
        $timestamp = Logger::Microtime();
        $filename = $_SERVER['DOCUMENT_ROOT'] . '/Files/TestKeys/keyfile_' . $timestamp . '.json';
        $this->m_files[] = 'http://' . $_SERVER['HTTP_HOST'] . '/Files/TestKeys/keyfile_' . $timestamp . '.json';

        if (file_exists($filename))
            unlink($filename);

        $file = fopen($filename, 'w');

        fwrite($file, "[\n");
        $first = true;
        foreach ($this->m_keys as $key) {
            if ($first) {
                fwrite($file, json_encode($key));
                $first = false;
            }
            else {
                fwrite($file,",\n" . json_encode($key));
            }
        }
        fwrite($file, "\n]");
        fclose($file);
    }
}