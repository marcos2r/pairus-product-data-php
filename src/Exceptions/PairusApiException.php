<?php

declare(strict_types=1);

namespace Pairus\Exceptions;

use Throwable;

/**
 * Exceção lançada quando a API retorna erro HTTP (4xx ou 5xx).
 * Mapeia os campos padronizados do layout SEFAZ/NF-e (cStat e xMotivo).
 */
class PairusApiException extends PairusException
{
    public readonly int $statusCode;
    public readonly string $cStat;
    public readonly string $xMotivo;
    public readonly ?string $detail;
    public readonly array $rawResponse;

    public function __construct(
        int $statusCode,
        ?string $cStat = null,
        ?string $xMotivo = null,
        ?string $detail = null,
        array $rawResponse = [],
        ?Throwable $previous = null
    ) {
        $this->statusCode = $statusCode;
        $this->cStat = $cStat ?? (string) $statusCode;
        $this->xMotivo = $xMotivo ?? $detail ?? "Erro HTTP {$statusCode}";
        $this->detail = $detail;
        $this->rawResponse = $rawResponse;

        $mensagem = "[{$this->cStat}] {$this->xMotivo}";
        parent::__construct($mensagem, $statusCode, $previous);
    }
}
