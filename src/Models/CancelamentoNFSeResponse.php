<?php

declare(strict_types=1);

namespace Pairus\Models;

/**
 * Representa a resposta estruturada de cancelamento de NFS-e emitida.
 */
class CancelamentoNFSeResponse
{
    public function __construct(
        public readonly bool $sucesso,
        public readonly string $numeroNfse,
        public readonly ?string $dataCancelamento = null,
        public readonly string $mensagem = '',
        public readonly array $erros = [],
        public readonly array $raw = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sucesso: (bool) ($data['sucesso'] ?? false),
            numeroNfse: (string) ($data['numero_nfse'] ?? $data['numeroNfse'] ?? ''),
            dataCancelamento: isset($data['data_cancelamento']) ? (string) $data['data_cancelamento'] : null,
            mensagem: (string) ($data['mensagem'] ?? ''),
            erros: (array) ($data['erros'] ?? []),
            raw: $data
        );
    }
}
