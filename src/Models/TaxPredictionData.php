<?php

declare(strict_types=1);

namespace Pairus\Models;

/**
 * Estrutura consolidada de impostos e parâmetros fiscais sugeridos para NF-e.
 *
 * Espelha `dados_tributarios` de /v2/fiscal/predict. A API devolve `cfop_interno`/`cfop_externo`,
 * o Imposto Seletivo em `is`, os avisos em `avisos_fiscais` e a alíquota unificada dentro de `ibscbs`;
 * os nomes da 1.4 (`cfop`, `cfopDevolucao`, `impostoSeletivo`, `aliquotaEfetivaUnificada`, `avisos`)
 * continuam preenchidos a partir deles. `cfop` recebe o CFOP interno: escolha `cfopExterno` em
 * operações interestaduais.
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
        public readonly ?string $icmsModalidadeBcSt,
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
        public readonly array $raw,
        public readonly ?string $cfopInterno = null,
        public readonly ?string $cfopExterno = null,
        public readonly ?string $cfopDevolucaoInterno = null,
        public readonly ?string $cfopDevolucaoExterno = null,
        public readonly ?string $origemMercadoria = null,
        public readonly bool $icmsSujeitoSt = false,
        public readonly float $icmsCreditoAliquotaSn = 0.0,
        public readonly ?string $extipi = null,
        public readonly ?string $reformaTributariaNota = null,
        public readonly array $diagnosticoAntiRejeicao = [],
        public readonly ?array $otimizacaoTributaria = null,
        public readonly ?array $difal = null
    ) {}

    public static function fromArray(array $data): self
    {
        $ibscbs = IBSCBSData::fromArray(is_array($data['ibscbs'] ?? null) ? $data['ibscbs'] : []);
        $cfopInterno = $data['cfop_interno'] ?? $data['cfop'] ?? null;
        $cfopDevolucaoInterno = $data['cfop_devolucao_interno'] ?? $data['cfop_devolucao'] ?? null;
        $impostoSeletivo = $data['is'] ?? $data['imposto_seletivo'] ?? null;
        $avisos = $data['avisos_fiscais'] ?? $data['avisos'] ?? [];

        return new self(
            ncmSugerido: (string) ($data['ncm_sugerido'] ?? ''),
            cestSugerido: isset($data['cest_sugerido']) ? (string) $data['cest_sugerido'] : null,
            cfop: (string) ($data['cfop'] ?? $cfopInterno ?? ''),
            cfopDevolucao: $cfopDevolucaoInterno !== null ? (string) ($data['cfop_devolucao'] ?? $cfopDevolucaoInterno) : null,
            icmsCstCsosn: (string) ($data['icms_cst_csosn'] ?? ''),
            icmsAliquota: (float) ($data['icms_aliquota'] ?? 0.0),
            icmsAliquotaSt: (float) ($data['icms_aliquota_st'] ?? 0.0),
            icmsMvaSt: (float) ($data['icms_mva_st'] ?? 0.0),
            icmsReducaoBc: (float) ($data['icms_reducao_bc'] ?? 0.0),
            icmsReducaoBcSt: (float) ($data['icms_reducao_bc_st'] ?? 0.0),
            icmsModalidadeBc: (string) ($data['icms_modalidade_bc'] ?? '3'),
            icmsModalidadeBcSt: isset($data['icms_modalidade_bc_st']) ? (string) $data['icms_modalidade_bc_st'] : null,
            pisCst: (string) ($data['pis_cst'] ?? ''),
            pisAliquota: (float) ($data['pis_aliquota'] ?? 0.0),
            cofinsCst: (string) ($data['cofins_cst'] ?? ''),
            cofinsAliquota: (float) ($data['cofins_aliquota'] ?? 0.0),
            ipiCst: isset($data['ipi_cst']) ? (string) $data['ipi_cst'] : null,
            ipiAliquota: (float) ($data['ipi_aliquota'] ?? 0.0),
            ibscbs: $ibscbs,
            impostoSeletivo: SelectiveTaxData::fromArray(is_array($impostoSeletivo) ? $impostoSeletivo : null),
            dadosCombustivel: FuelData::fromArray($data['dados_combustivel'] ?? null),
            beneficioFiscal: isset($data['beneficio_fiscal']) ? (string) $data['beneficio_fiscal'] : null,
            aliquotaEfetivaUnificada: (float) ($data['aliquota_efetiva_unificada'] ?? $ibscbs->aliquotaEfetivaUnificada),
            motorIa: (string) ($data['motor_ia'] ?? 'pairus_fiscal_engine'),
            avisos: is_array($avisos) ? $avisos : [],
            raw: $data,
            cfopInterno: $cfopInterno !== null ? (string) $cfopInterno : null,
            cfopExterno: isset($data['cfop_externo']) ? (string) $data['cfop_externo'] : null,
            cfopDevolucaoInterno: $cfopDevolucaoInterno !== null ? (string) $cfopDevolucaoInterno : null,
            cfopDevolucaoExterno: isset($data['cfop_devolucao_externo']) ? (string) $data['cfop_devolucao_externo'] : null,
            origemMercadoria: isset($data['origem_mercadoria']) ? (string) $data['origem_mercadoria'] : null,
            icmsSujeitoSt: (bool) ($data['icms_sujeito_st'] ?? false),
            icmsCreditoAliquotaSn: (float) ($data['icms_credito_aliquota_sn'] ?? 0.0),
            extipi: isset($data['extipi']) ? (string) $data['extipi'] : null,
            reformaTributariaNota: isset($data['reforma_tributaria_nota']) ? (string) $data['reforma_tributaria_nota'] : null,
            diagnosticoAntiRejeicao: is_array($data['diagnostico_anti_rejeicao'] ?? null) ? $data['diagnostico_anti_rejeicao'] : [],
            otimizacaoTributaria: is_array($data['otimizacao_tributaria'] ?? null) ? $data['otimizacao_tributaria'] : null,
            difal: is_array($data['difal'] ?? null) ? $data['difal'] : null
        );
    }
}
