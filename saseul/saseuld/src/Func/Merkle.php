<?php

namespace src\Func;

class Merkle
{
    private $m_original_array;
    private $m_hash_array;

    function __construct()
    {
        $this->m_original_array = [];
        $this->m_hash_array = [];
    }

    function MakeMerkleHash($array)
    {
        $this->m_original_array = $array;
        $this->m_hash_array = array();

        do {
            if (count($this->m_original_array) > 1) {
                for ($i = 0; $i < count($this->m_original_array); $i = $i + 2) {
                    if ($i + 1 == count($this->m_original_array)) {
                        if (!is_string($this->m_original_array[$i]))
                            $this->m_original_array[$i] = json_encode($this->m_original_array[$i]);

                        $this->m_hash_array[count($this->m_hash_array) - 1] = hash('sha256', $this->m_hash_array[count($this->m_hash_array) - 1] . $this->m_original_array[$i]);
                    } else {
                        if (!is_string($this->m_original_array[$i]))
                            $this->m_original_array[$i] = json_encode($this->m_original_array[$i]);

                        if (!is_string($this->m_original_array[$i + 1]))
                            $this->m_original_array[$i + 1] = json_encode($this->m_original_array[$i + 1]);

                        $this->m_hash_array[] = hash('sha256', $this->m_original_array[$i] . $this->m_original_array[$i + 1]);
                    }
                }

            } else if (count($this->m_original_array) == 1) {
                if (!is_string($this->m_original_array[0]))
                    $this->m_original_array[0] = json_encode($this->m_original_array[0]);

                $this->m_hash_array[] = hash('sha256', $this->m_original_array[0]);
            } else {
                $this->m_hash_array[] = hash('sha256', '');
            }

            $this->m_original_array = $this->m_hash_array;
            $this->m_hash_array = array();
        } while (count($this->m_original_array) > 1);

        return $this->m_original_array[0];
    }

    function MakeBlockHash($last_blockhash, $transaction_hash)
    {
        return hash('sha256', $last_blockhash . $transaction_hash);
    }
}