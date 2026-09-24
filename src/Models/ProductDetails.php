<?php

declare(strict_types=1);

namespace Pairus\Models;

/**
 * Modelo de dados com detalhes completos de um produto catalogado na PAIRUS.
 *
 * A API devolve marca, categoria, imagem e pesos dentro de `infoAdicional` (xMarca, xCategoria,
 * urlImagem, pesoBruto, pesoLiquido); até a 1.4 esses campos ficavam nulos no SDK.
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
        public readonly array $raw = [],
        public readonly ?string $tpGTIN = null,
        public readonly ?string $fonte = null,
        public readonly ?array $infoAdicional = null
    ) {}

    public static function fromArray(array $data, string $fallbackGtin = ''): self
    {
        $prod = $data['produto'] ?? $data;
        $info = is_array($prod['infoAdicional'] ?? null) ? $prod['infoAdicional'] : [];
        $marca = $prod['marca'] ?? $info['xMarca'] ?? null;
        $categoria = $prod['categoria'] ?? $info['xCategoria'] ?? null;
        $imagem = $prod['imagem_url'] ?? $prod['thumbnail'] ?? $info['urlImagem'] ?? null;
        $pesoBruto = $prod['peso_bruto'] ?? $info['pesoBruto'] ?? null;
        $pesoLiquido = $prod['peso_liquido'] ?? $info['pesoLiquido'] ?? null;

        return new self(
            gtin: (string) ($prod['GTIN'] ?? $prod['gtin'] ?? $fallbackGtin),
            xProd: (string) ($prod['xProd'] ?? $prod['descricao'] ?? 'Descrição Indisponível'),
            ncm: isset($prod['NCM']) || isset($prod['ncm']) ? (string) ($prod['NCM'] ?? $prod['ncm']) : null,
            cest: isset($prod['CEST']) || isset($prod['cest']) ? (string) ($prod['CEST'] ?? $prod['cest']) : null,
            marca: $marca !== null ? (string) $marca : null,
            fabricante: isset($prod['fabricante']) ? (string) $prod['fabricante'] : null,
            categoria: $categoria !== null ? (string) $categoria : null,
            imagemUrl: $imagem !== null ? (string) $imagem : null,
            pesoBruto: is_numeric($pesoBruto) ? (float) $pesoBruto : null,
            pesoLiquido: is_numeric($pesoLiquido) ? (float) $pesoLiquido : null,
            quantidadeEmbalagem: isset($prod['quantidade_embalagem']) ? (int) $prod['quantidade_embalagem'] : 1,
            raw: $data,
            tpGTIN: isset($prod['tpGTIN']) ? (string) $prod['tpGTIN'] : null,
            fonte: isset($prod['fonte']) ? (string) $prod['fonte'] : null,
            infoAdicional: $info !== [] ? $info : null
        );
    }
}
