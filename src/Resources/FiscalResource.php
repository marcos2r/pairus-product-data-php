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
     * @param float|null $aliquotaCreditoIcmsSn Alíquota de crédito de ICMS do Simples Nacional (pCredSN)
     * @param bool|null $destinatarioSuframa Se o destinatário tem inscrição SUFRAMA ativa (ZFM/ALC)
     * @param string|null $inscricaoSuframa Inscrição SUFRAMA do adquirente
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
        ?array $itens = null,
        ?float $aliquotaCreditoIcmsSn = null,
        ?bool $destinatarioSuframa = null,
        ?string $inscricaoSuframa = null
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
        if ($aliquotaCreditoIcmsSn !== null) {
            $payload['aliquota_credito_icms_sn'] = $aliquotaCreditoIcmsSn;
        }
        if ($destinatarioSuframa !== null) {
            $payload['destinatario_suframa'] = $destinatarioSuframa;
        }
        if ($inscricaoSuframa !== null) {
            $payload['inscricao_suframa'] = $inscricaoSuframa;
        }

        $data = $this->request('POST', '/v2/fiscal/predict', jsonData: $payload);

        return TaxPredictionResponse::fromArray($data);
    }

    /**
     * Audita cadastros de produtos em lote (/v2/fiscal/sanitize): vigência do NCM, CEST e alíquotas.
     *
     * Cada item precisa de `NCM`; `CEST`, `xProd` e `id` são opcionais. Os nomes `ncm`, `cest`,
     * `descricao` e `gtin` também são aceitos e convertidos (até a 1.4 o SDK chamava uma rota
     * inexistente, /api/v1/fiscal/saneamento, com nomes que a API recusava).
     *
     * @param array $itens Lista de itens (arrays associativos)
     * @return array `status`, `total_itens` e `itens` (cada um com `NCM_informado`, `CEST_informado` e `auditoria_fiscal`)
     */
    public function sanitize(array $itens): array
    {
        $apenasDigitos = static fn($valor): ?string => $valor !== null && $valor !== ''
            ? preg_replace('/\D/', '', (string) $valor)
            : null;

        $payload = array_map(static function (array $item) use ($apenasDigitos): array {
            return array_filter([
                'NCM' => $apenasDigitos($item['NCM'] ?? $item['ncm'] ?? null),
                'CEST' => $apenasDigitos($item['CEST'] ?? $item['cest'] ?? null),
                'xProd' => $item['xProd'] ?? $item['descricao'] ?? null,
                'id' => isset($item['id']) ? (string) $item['id'] : $apenasDigitos($item['gtin'] ?? null),
            ], fn($v) => $v !== null);
        }, $itens);

        return $this->request('POST', '/v2/fiscal/sanitize', jsonData: ['itens' => array_values($payload)]);
    }
}
