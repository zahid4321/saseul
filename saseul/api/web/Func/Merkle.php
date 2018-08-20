<?php

namespace Func;

class Merkle
{
    function MakeMerkleHash($array)
    {
        $original_array = $array;
        $hash_array = array();

        do {
            if (count($original_array) > 1) {
                for ($i = 0; $i < count($original_array); $i = $i + 2) {
                    if ($i + 1 == count($original_array)) {
                        if (!is_string($original_array[$i]))
                            $original_array[$i] = json_encode($original_array[$i]);

                        $hash_array[count($hash_array) - 1] = sha1($hash_array[count($hash_array) - 1] . $original_array[$i]);
                    } else {
                        if (!is_string($original_array[$i]))
                            $original_array[$i] = json_encode($original_array[$i]);

                        if (!is_string($original_array[$i + 1]))
                            $original_array[$i + 1] = json_encode($original_array[$i + 1]);

                        $hash_array[] = sha1($original_array[$i] . $original_array[$i + 1]);
                    }
                }

            } else if (count($original_array) == 1) {
                if (!is_string($original_array[0]))
                    $original_array[0] = json_encode($original_array[0]);

                $hash_array[] = sha1($original_array[0]);
            } else {
                $hash_array[] = sha1('');
            }

            $original_array = $hash_array;
            $hash_array = array();
        } while (count($original_array) > 1);

        return $original_array[0];
    }

    function MakeBlockHash($last_blockhash, $transaction_hash)
    {
        return hash('sha256', $last_blockhash . $transaction_hash);
    }
}