<?php

declare(strict_types=1);

namespace Pairus\Resources;

use Pairus\Models\ProductDetails;

/**
 * Namespace de recursos para consulta de catálogo GTIN/EAN.
 */
class ProductsResource extends BaseResource
{
    /**
     * Consulta um produto cadastrado no catálogo global da PAIRUS por código GTIN/EAN.
     *
     * @param string|int $gtin Código GTIN/EAN numérico (8, 12, 13 ou 14 dígitos).
     * @return ProductDetails Detalhes consolidados do produto.
     */
    public function get(string|int $gtin): ProductDetails
    {
        $gtinLimpo = preg_replace('/\D/', '', (string) $gtin) ?? '';
        $data = $this->request('GET', "/api/produtos/{$gtinLimpo}");

        return ProductDetails::fromArray($data, $gtinLimpo);
    }
}
