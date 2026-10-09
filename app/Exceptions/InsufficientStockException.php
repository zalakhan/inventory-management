<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct(
        public readonly string $productName,
        public readonly string $available,
        public readonly string $requested,
    ) {
        parent::__construct(
            "Insufficient stock for {$productName}. Available: {$available}, requested: {$requested}."
        );
    }
}
