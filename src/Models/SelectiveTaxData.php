<?php

declare(strict_types=1);

namespace Pairus\Models;

/**
 * Detalhamento do Imposto Seletivo (IS), devolvido pela API na chave `is` de `dados_tributarios`
 * (`CSTIS`, `cClassTribIS`, `pIS`). Os nomes da 1.4 (`cst`, `aliquota`) continuam preenchidos.
 */
class SelectiveTaxData
{
    public function __construct(
        public readonly ?string $cst = null,
        public readonly ?string $cClassTribIS = null,
        public readonly float $aliquota = 0.0,
        public readonly bool $incidencia = false,
        public readonly ?string $nota = null
    ) {}

    public static function fromArray(?array $data): ?self
    {
        if ($data === null) {
            return null;
        }

        $cst = $data['CSTIS'] ?? $data['cst'] ?? null;
        $cClassTrib = $data['cClassTribIS'] ?? $data['is_cClassTribIS'] ?? null;

        return new self(
            cst: $cst !== null ? str_pad((string) $cst, 3, '0', STR_PAD_LEFT) : null,
            cClassTribIS: $cClassTrib !== null ? str_pad((string) $cClassTrib, 6, '0', STR_PAD_LEFT) : null,
            aliquota: (float) ($data['pIS'] ?? $data['aliquota'] ?? 0.0),
            incidencia: (bool) ($data['incidencia'] ?? ($cst !== null)),
            nota: isset($data['nota']) ? (string) $data['nota'] : null
        );
    }
}
