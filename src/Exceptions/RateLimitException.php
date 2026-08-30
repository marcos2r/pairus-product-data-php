<?php

declare(strict_types=1);

namespace Pairus\Exceptions;

use Throwable;

/**
 * Erro de limite de requisições excedido (HTTP 429).
 */
class RateLimitException extends PairusApiException
{
    public readonly ?float $retryAfter;

    public function __construct(
        int $statusCode = 429,
        ?float $retryAfter = null,
        ?string $cStat = "429",
        ?string $xMotivo = "Limite de requisições excedido. Tente novamente mais tarde.",
        ?string $detail = null,
        array $rawResponse = [],
        ?Throwable $previous = null
    ) {
        $this->retryAfter = $retryAfter;
        parent::__construct($statusCode, $cStat, $xMotivo, $detail, $rawResponse, $previous);
    }
}
