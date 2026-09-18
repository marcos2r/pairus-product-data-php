<?php

declare(strict_types=1);

namespace Pairus\Models;

/**
 * Detalhamento de dados de combustíveis exigidos pela ANP para o XML da NF-e.
 */
class FuelData
{
    public function __construct(
        public readonly string $codigoAnp,
        public readonly string $descricaoAnp,
        public readonly float $percGlp = 0.0,
        public readonly float $percGasNacional = 0.0,
        public readonly float $percGasImportado = 0.0,
        public readonly float $valorPorKg = 0.0
    ) {}

    public static function fromArray(?array $data): ?self
    {
        if ($data === null) {
            return null;
        }

        return new self(
            codigoAnp: (string) ($data['codigo_anp'] ?? ''),
            descricaoAnp: (string) ($data['descricao_anp'] ?? ''),
            percGlp: (float) ($data['perc_glp'] ?? 0.0),
            percGasNacional: (float) ($data['perc_gas_nacional'] ?? 0.0),
            percGasImportado: (float) ($data['perc_gas_importado'] ?? 0.0),
            valorPorKg: (float) ($data['valor_por_kg'] ?? 0.0)
        );
    }
}
