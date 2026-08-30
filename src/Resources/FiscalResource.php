<?php

declare(strict_types=1);

namespace Pairus\Resources;

use Pairus\Models\TaxPredictionResponse;

/**
 * Namespace de recursos para o motor de predição fiscal e Reforma Tributária.
 */
class FiscalResource extends BaseResource
{
    /**
     * Executa a predição fiscal completa (IBS, CBS, Imposto Seletivo, ICMS, IPI, PIS, COFINS e regras anti-rejeição).
     *
     * @param string $regimeTributario 'simples_nacional', 'lucro_presumido' ou 'lucro_real'
     * @param string $ufOrigem Sigla da UF emissora da mercadoria (2 letras)
     * @param string|null $ufDestino Sigla da UF destinatária (2 letras). Se nulo, assume a UF de origem.
     * @param string $finalidade 'revenda', 'consumo_final' ou 'industrializacao'
     * @param bool $destinatarioContribuinte Se o destinatário é contribuinte do ICMS
     * @param string|null $xProd Descrição do produto (modo individual)
     * @param string|null $gtin Código de barras (modo individual)
     * @param string|null $ncm Código NCM (modo individual)
     * @param string|null $cest Código CEST (modo individual)
     * @param array|null $itens Lista de itens para cálculo em lote da Nota Fiscal
     * @return TaxPredictionResponse Estrutura tributária completa e validada
     */
    public function predict(
        string $regimeTributario,
        string $ufOrigem,
        ?string $ufDestino = null,
        string $finalidade = 'revenda',
        bool $destinatarioContribuinte = true,
        ?string $xProd = null,
        ?string $gtin = null,
        ?string $ncm = null,
        ?string $cest = null,
        ?array $itens = null
    ): TaxPredictionResponse {
        $payload = [
            'regime_tributario' => $regimeTributario,
            'uf_origem' => strtoupper(trim($ufOrigem)),
            'uf_destino' => strtoupper(trim($ufDestino ?? $ufOrigem)),
            'finalidade' => $finalidade,
            'destinatario_contribuinte' => $destinatarioContribuinte,
        ];

        if ($xProd !== null) {
            $payload['xProd'] = $xProd;
        }
        if ($gtin !== null) {
            $payload['gtin'] = preg_replace('/\D/', '', $gtin);
        }
        if ($ncm !== null) {
            $payload['ncm'] = preg_replace('/\D/', '', $ncm);
        }
        if ($cest !== null) {
            $payload['cest'] = preg_replace('/\D/', '', $cest);
        }
        if ($itens !== null) {
            $payload['itens'] = $itens;
        }

        $data = $this->request('POST', '/v2/fiscal/predict', jsonData: $payload);

        return TaxPredictionResponse::fromArray($data);
    }
}
