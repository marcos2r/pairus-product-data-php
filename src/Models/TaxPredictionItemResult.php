<?php

declare(strict_types=1);

namespace Pairus\Models;

/**
 * Resultado individual de um item na predição em lote.
 */
class TaxPredictionItemResult
{
    public function __construct(
        public readonly int $itemIndex,
        public readonly ?string $xProd,
        public readonly TaxPredictionData $dadosTributarios
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            itemIndex: (int) ($data['item_index'] ?? 0),
            xProd: isset($data['xProd']) ? (string) $data['xProd'] : null,
            dadosTributarios: TaxPredictionData::fromArray($data['dados_tributarios'] ?? [])
        );
    }
}
