<?php

declare(strict_types=1);

namespace Pairus\Exceptions;

use Exception;
use Throwable;

/**
 * Exceção base para todos os erros gerados pelo SDK PAIRUS.
 */
class PairusException extends Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
