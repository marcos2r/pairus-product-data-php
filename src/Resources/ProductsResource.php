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
        $data = $this->request('GET', "/v1/gtin/{$gtinLimpo}");

        return ProductDetails::fromArray($data, $gtinLimpo);
    }

    /**
     * Consulta cadastral enriquecida (SEO, dimensões logísticas, imagem em alta resolução e atributos).
     *
     * @param string|int $gtin Código GTIN/EAN numérico
     * @return array Resposta enriquecida com atributos avançados
     */
    public function getEnriched(string|int $gtin): array
    {
        $gtinLimpo = preg_replace('/\D/', '', (string) $gtin) ?? '';

        return $this->request('GET', "/v2/gtin/{$gtinLimpo}");
    }

    /**
     * Realiza busca textual e semântica por inteligência artificial no catálogo global de produtos.
     *
     * @param string $query Termo de busca, nome do produto ou marca
     * @param int $limite Quantidade máxima de resultados (1 a 50)
     * @return array Itens encontrados com score de similaridade
     */
    public function search(string $query, int $limite = 10): array
    {
        return $this->request('GET', '/v2/gtin/search', [
            'q' => $query,
        ]);
    }

    /**
     * Identifica produtos e códigos de barras a partir de imagem fotográfica (OCR + Visão Computacional).
     *
     * @param string $caminhoOuBytes Caminho para o arquivo no disco ou string contendo os bytes brutos da imagem
     * @param string|null $nomeArquivo Nome do arquivo opcional (ex: 'produto.jpg')
     * @param string|null $tipo Tipo opcional de OCR ('gtin', 'rotulo', 'nfe')
     * @return array Detalhes do produto identificado via imagem
     */
    public function scan(string $caminhoOuBytes, ?string $nomeArquivo = null, ?string $tipo = null): array
    {
        if (is_file($caminhoOuBytes)) {
            $filename = $nomeArquivo ?? basename($caminhoOuBytes);
            $file = new \CURLFile($caminhoOuBytes, null, $filename);
        } else {
            $filename = $nomeArquivo ?? 'produto.jpg';
            $file = new \CURLStringFile($caminhoOuBytes, $filename, 'image/jpeg');
        }

        $files = ['file' => $file];
        $formData = [];
        if ($tipo !== null) {
            $formData['tipo'] = $tipo;
        }

        return $this->request('POST', '/v2/gtin/scan', files: $files, formData: $formData);
    }
}
