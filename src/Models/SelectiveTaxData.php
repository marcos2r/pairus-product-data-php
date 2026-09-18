<?php

declare(strict_types=1);

namespace Pairus\Models;

/**
 * Detalhamento do Imposto Seletivo (IS).
 */
class SelectiveTaxData
{
    public function __construct(
        public readonly string $cst = '001',
        public readonly string $cClassTribIS = '000101',
        public readonly float $aliquota = 0.0
    ) {}

    public static function fromArray(?array $data): ?self
    {
        if ($data === null) {
            return null;
        }

        return new self(
            cst: str_pad((string) ($data['cst'] ?? $data['CSTIS'] ?? '001'), 3, '0', STR_PAD_LEFT),
            cClassTribIS: str_pad((string) ($data['cClassTribIS'] ?? $data['is_cClassTribIS'] ?? '000101'), 6, '0', STR_PAD_LEFT),
            aliquota: (float) ($data['aliquota'] ?? 0.0)
        );
    }
}
