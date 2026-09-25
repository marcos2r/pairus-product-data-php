<?php

declare(strict_types=1);

namespace Pairus\Models;

/**
 * Detalhamento do IBS e CBS da Reforma Tributária.
 */
class IBSCBSData
{
    public function __construct(
        public readonly string $cst,
        public readonly string $cClassTrib,
        public readonly float $cbsAliquota = 8.8,
        public readonly float $cbsDiferimento = 0.0,
        public readonly float $cbsReducaoAliquota = 0.0,
        public readonly float $cbsAliquotaEfetiva = 8.8,
        public readonly float $ibsAliquotaEstadual = 10.0,
        public readonly float $ibsAliquotaMunicipal = 7.7,
        public readonly float $ibsDiferimento = 0.0,
        public readonly float $ibsReducaoAliquota = 0.0,
        public readonly float $ibsAliquotaEfetiva = 17.7,
        public readonly float $aliquotaEfetivaUnificada = 0.0,
        public readonly float $ibsReducaoAliquotaEstadual = 0.0,
        public readonly float $ibsReducaoAliquotaMunicipal = 0.0,
        public readonly bool $vTotDFeObrigatorio = false
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cst: str_pad((string) ($data['cst'] ?? '000'), 3, '0', STR_PAD_LEFT),
            cClassTrib: str_pad((string) ($data['cClassTrib'] ?? '000001'), 6, '0', STR_PAD_LEFT),
            cbsAliquota: (float) ($data['cbs_aliquota'] ?? 8.8),
            cbsDiferimento: (float) ($data['cbs_diferimento'] ?? 0.0),
            cbsReducaoAliquota: (float) ($data['cbs_reducao_aliquota'] ?? 0.0),
            cbsAliquotaEfetiva: (float) ($data['cbs_aliquota_efetiva'] ?? 8.8),
            ibsAliquotaEstadual: (float) ($data['ibs_aliquota_estadual'] ?? 10.0),
            ibsAliquotaMunicipal: (float) ($data['ibs_aliquota_municipal'] ?? 7.7),
            ibsDiferimento: (float) ($data['ibs_diferimento'] ?? 0.0),
            ibsReducaoAliquota: (float) ($data['ibs_reducao_aliquota'] ?? 0.0),
            ibsAliquotaEfetiva: (float) ($data['ibs_aliquota_efetiva'] ?? 17.7),
            aliquotaEfetivaUnificada: (float) ($data['aliquota_efetiva_unificada'] ?? 0.0),
            ibsReducaoAliquotaEstadual: (float) ($data['ibs_reducao_aliquota_estadual'] ?? 0.0),
            ibsReducaoAliquotaMunicipal: (float) ($data['ibs_reducao_aliquota_municipal'] ?? 0.0),
            vTotDFeObrigatorio: (bool) ($data['vTotDFe_obrigatorio'] ?? false)
        );
    }
}
