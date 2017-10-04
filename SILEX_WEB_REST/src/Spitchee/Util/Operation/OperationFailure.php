<?php

namespace Spitchee\Util\Operation;

class OperationFailure implements OperationResult
{
    const REASON_TYPE_SERVER = 1;
    const REASON_TYPE_CLIENT = 2;

    /** @var int $reason */
    private $reason;

    /** @var string|null $details */
    private $details;

    private function __construct($reason, $details)
    {
        $this->reason   = $reason;
        $this->details  = $details;
    }

    public static function fromServer($details = null)
    {
        return new self(self::REASON_TYPE_SERVER, $details);
    }

    public static function fromClient($details = null)
    {
        return new self(self::REASON_TYPE_CLIENT, $details);
    }

    public function isSuccessfull()
    {
        return false;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function getReason()
    {
        return $this->reason;
    }
}