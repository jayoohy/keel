<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientBalanceException extends RuntimeException
{
    public function __construct(public readonly float $requested, public readonly float $available)
    {
        parent::__construct("Cannot allocate {$requested}: only {$available} is unallocated.");
    }
}
