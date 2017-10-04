<?php

namespace Spitchee\Util\Operation;

class OperationSuccess implements OperationResult
{
    public function isSuccessfull()
    {
        return true;
    }

    public static function create()
    {
        return new self();
    }
}