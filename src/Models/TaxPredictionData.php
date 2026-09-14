<?php

declare(strict_types=1);

namespace Pairus\Models;

/**
 * Estrutura consolidada de impostos e parâmetros fiscais sugeridos para NF-e.
 */
class TaxPredictionData
{
    public function __construct(
        public readonly string $ncmSugerido,
        public readonly ?string $cestSugerido,
        public readonly string $cfop,
        public readonly ?string $cfopDevolucao,
        public readonly string $icmsCstCsosn,
        public readonly float $icmsAliquota,
        public readonly float $icmsAliquotaSt,
        public readonly float $icmsMvaSt,
        public readonly float $icmsReducaoBc,
        public readonly float $icmsReducaoBcSt,
        public readonly string $icmsModalidadeBc,
        public readonly string $icmsModalidadeBcSt,
        public readonly string $pisCst,
        public readonly float $pisAliquota,
        public readonly string $cofinsCst,
        public readonly float $cofinsAliquota,
        public readonly ?string $ipiCst,
        public readonly float $ipiAliquota,
        public readonly IBSCBSData $ibscbs,
        public readonly ?SelectiveTaxData $impostoSeletivo,
        public readonly ?FuelData $dadosCombustivel,
        public readonly ?string $beneficioFiscal,
        public readonly float $aliquotaEfetivaUnificada,
        public readonly string $motorIa,
        public readonly array $avisos,
        public readonly array $raw
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            ncmSugerido: (string) ($data['ncm_sugerido'] ?? ''),
            cestSugerido: isset($data['cest_sugerido']) ? (string) $data['cest_sugerido'] : null,
            cfop: (string) ($data['cfop'] ?? ''),
            cfopDevolucao: isset($data['cfop_devolucao']) ? (string) $data['cfop_devolucao'] : null,
            icmsCstCsosn: (string) ($data['icms_cst_csosn'] ?? ''),
            icmsAliquota: (float) ($data['icms_aliquota'] ?? 0.0),
            icmsAliquotaSt: (float) ($data['icms_aliquota_st'] ?? 0.0),
            icmsMvaSt: (float) ($data['icms_mva_st'] ?? 0.0),
            icmsReducaoBc: (float) ($data['icms_reducao_bc'] ?? 0.0),
            icmsReducaoBcSt: (float) ($data['icms_reducao_bc_st'] ?? 0.0),
            icmsModalidadeBc: (string) ($data['icms_modalidade_bc'] ?? '3'),
            icmsModalidadeBcSt: (string) ($data['icms_modalidade_bc_st'] ?? '4'),
            pisCst: (string) ($data['pis_cst'] ?? ''),
            pisAliquota: (float) ($data['pis_aliquota'] ?? 0.0),
            cofinsCst: (string) ($data['cofins_cst'] ?? ''),
            cofinsAliquota: (float) ($data['cofins_aliquota'] ?? 0.0),
            ipiCst: isset($data['ipi_cst']) ? (string) $data['ipi_cst'] : null,
            ipiAliquota: (float) ($data['ipi_aliquota'] ?? 0.0),
            ibscbs: IBSCBSData::fromArray($data['ibscbs'] ?? []),
            impostoSeletivo: SelectiveTaxData::fromArray($data['imposto_seletivo'] ?? null),
            dadosCombustivel: FuelData::fromArray($data['dados_combustivel'] ?? null),
            beneficioFiscal: isset($data['beneficio_fiscal']) ? (string) $data['beneficio_fiscal'] : null,
            aliquotaEfetivaUnificada: (float) ($data['aliquota_efetiva_unificada'] ?? 0.0),
            motorIa: (string) ($data['motor_ia'] ?? 'pairus_fiscal_engine'),
            avisos: is_array($data['avisos'] ?? null) ? $data['avisos'] : [],
            raw: $data
        );
    }
}
