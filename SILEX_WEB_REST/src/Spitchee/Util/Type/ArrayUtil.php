<?php

namespace Spitchee\Util\Type;

class ArrayUtil
{
    static public function asCleanNumericArray($array)
    {
        $cleaned = array();

        foreach ($array as $val) {
            $cleaned[] = $val;
        }

        return $cleaned;
    }
}