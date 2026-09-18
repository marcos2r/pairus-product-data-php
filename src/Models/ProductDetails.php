<?php

declare(strict_types=1);

namespace Pairus\Models;

/**
 * Modelo de dados com detalhes completos de um produto catalogado na PAIRUS.
 */
class ProductDetails
{
    public function __construct(
        public readonly string $gtin,
        public readonly string $xProd,
        public readonly ?string $ncm = null,
        public readonly ?string $cest = null,
        public readonly ?string $marca = null,
        public readonly ?string $fabricante = null,
        public readonly ?string $categoria = null,
        public readonly ?string $imagemUrl = null,
        public readonly ?float $pesoBruto = null,
        public readonly ?float $pesoLiquido = null,
        public readonly int $quantidadeEmbalagem = 1,
        public readonly array $raw = []
    ) {}

    public static function fromArray(array $data, string $fallbackGtin = ''): self
    {
        $prod = $data['produto'] ?? $data;

        return new self(
            gtin: (string) ($prod['GTIN'] ?? $prod['gtin'] ?? $fallbackGtin),
            xProd: (string) ($prod['xProd'] ?? $prod['descricao'] ?? 'Descrição Indisponível'),
            ncm: isset($prod['NCM']) || isset($prod['ncm']) ? (string) ($prod['NCM'] ?? $prod['ncm']) : null,
            cest: isset($prod['CEST']) || isset($prod['cest']) ? (string) ($prod['CEST'] ?? $prod['cest']) : null,
            marca: isset($prod['marca']) ? (string) $prod['marca'] : null,
            fabricante: isset($prod['fabricante']) ? (string) $prod['fabricante'] : null,
            categoria: isset($prod['categoria']) ? (string) $prod['categoria'] : null,
            imagemUrl: isset($prod['imagem_url']) || isset($prod['thumbnail']) ? (string) ($prod['imagem_url'] ?? $prod['thumbnail']) : null,
            pesoBruto: isset($prod['peso_bruto']) && is_numeric($prod['peso_bruto']) ? (float) $prod['peso_bruto'] : null,
            pesoLiquido: isset($prod['peso_liquido']) && is_numeric($prod['peso_liquido']) ? (float) $prod['peso_liquido'] : null,
            quantidadeEmbalagem: isset($prod['quantidade_embalagem']) ? (int) $prod['quantidade_embalagem'] : 1,
            raw: $data
        );
    }
}
