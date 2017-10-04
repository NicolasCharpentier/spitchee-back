<?php

$result = [];
foreach ($argv as $c => $arg) {
    if (! $c)
        continue;
    if (false !== ($pos = strpos($arg, '='))) {
        $result[substr($arg, 0, $pos)] = substr($arg, $pos + 1);
    } else {
        $result[$arg] = true;
    }
}

return $result;