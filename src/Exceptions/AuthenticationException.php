<?php

declare(strict_types=1);

namespace Pairus\Exceptions;

use Throwable;

/**
 * Erro de autenticação (HTTP 401 - Chave de API inválida, revogada ou ausente).
 */
class AuthenticationException extends PairusApiException
{
    public function __construct(
        int $statusCode = 401,
        ?string $cStat = "401",
        ?string $xMotivo = "Chave de API inválida ou ausente.",
        ?string $detail = null,
        array $rawResponse = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($statusCode, $cStat, $xMotivo, $detail, $rawResponse, $previous);
    }
}
