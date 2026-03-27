<?php

namespace Cartino\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    protected $message = 'Insufficient stock available.';
}
