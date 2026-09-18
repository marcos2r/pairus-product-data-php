<?php

declare(strict_types=1);

namespace Pairus\Resources;

use Pairus\Models\CancelamentoNFSeResponse;
use Pairus\Models\NFSeResponse;

/**
 * Namespace de recursos para emissão, simulação, consulta e cancelamento de NFS-e Nacional.
 */
class NFSeResource extends BaseResource
{
    /**
     * Emite uma Nota Fiscal de Serviço eletrônica (NFS-e) Nacional.
     *
     * @param array $payload Dados estruturados da NFS-e (prestador, tomador, servico, valores)
     * @return NFSeResponse Objeto consolidado com status, protocolo, chave e links
     */
    public function emitir(array $payload): NFSeResponse
    {
        $data = $this->request('POST', '/v1/nfse/emitir', jsonData: $payload);

        return NFSeResponse::fromArray($data);
    }

    /**
     * Simula o cálculo de impostos e validação prévia de uma NFS-e sem transmissão à Prefeitura/Receita.
     *
     * @param array $payload Dados estruturados da NFS-e
     * @return NFSeResponse Pré-cálculo com alíquotas e tributos retidos
     */
    public function simular(array $payload): NFSeResponse
    {
        $data = $this->request('POST', '/v1/nfse/simular', jsonData: $payload);

        return NFSeResponse::fromArray($data);
    }

    /**
     * Cancela uma NFS-e autorizada perante a autoridade fiscal.
     *
     * @param array $payload Array com 'chave_acesso' ou 'numero_nfse' e 'codigo_cancelamento' / 'motivo'
     * @return CancelamentoNFSeResponse
     */
    public function cancelar(array $payload): CancelamentoNFSeResponse
    {
        $data = $this->request('POST', '/v1/nfse/cancelar', jsonData: $payload);

        return CancelamentoNFSeResponse::fromArray($data);
    }

    /**
     * Consulta a situação de uma NFS-e pelo número ou chave de acesso.
     *
     * @param string $chaveOuNumero Chave de acesso de 50 dígitos ou número do DPS/NFS-e
     * @return NFSeResponse
     */
    public function consultar(string $chaveOuNumero): NFSeResponse
    {
        $chaveLimpa = rawurlencode(trim($chaveOuNumero));
        $data = $this->request('GET', "/v1/nfse/{$chaveLimpa}");

        return NFSeResponse::fromArray($data);
    }
}
