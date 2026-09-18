<?php

declare(strict_types=1);

namespace Pairus\Models;

/**
 * Representa a resposta estruturada de cancelamento de NFS-e emitida.
 */
class CancelamentoNFSeResponse
{
    public function __construct(
        public readonly bool ,
        public readonly string ,
        public readonly ?string  = null,
        public readonly string  = '',
        public readonly array  = [],
        public readonly array  = []
    ) {}

    public static function fromArray(array ): self
    {
        return new self(
            sucesso: (bool) (['sucesso'] ?? false),
            numeroNfse: (string) (['numero_nfse'] ?? ['numeroNfse'] ?? ''),
            dataCancelamento: isset(['data_cancelamento']) ? (string) ['data_cancelamento'] : null,
            mensagem: (string) (['mensagem'] ?? ''),
            erros: (array) (['erros'] ?? []),
            raw: 
        );
    }
}
