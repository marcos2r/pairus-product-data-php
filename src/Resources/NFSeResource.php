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
     * @param array  Dados estruturados da NFS-e (prestador, tomador, servico, valores)
     * @return NFSeResponse Objeto consolidado com status, protocolo, chave e links
     */
    public function emitir(array ): NFSeResponse
    {
         = ->request('POST', '/api/v1/nfse/emitir', jsonData: );

        return NFSeResponse::fromArray();
    }

    /**
     * Simula o cálculo de impostos e validação prévia de uma NFS-e sem transmissão à Prefeitura/Receita.
     *
     * @param array  Dados estruturados da NFS-e
     * @return NFSeResponse Pré-cálculo com alíquotas e tributos retidos
     */
    public function simular(array ): NFSeResponse
    {
         = ->request('POST', '/api/v1/nfse/simular', jsonData: );

        return NFSeResponse::fromArray();
    }

    /**
     * Cancela uma NFS-e autorizada perante a autoridade fiscal.
     *
     * @param array  Array com 'chave_acesso' ou 'numero_nfse' e 'codigo_cancelamento' / 'motivo'
     * @return CancelamentoNFSeResponse
     */
    public function cancelar(array ): CancelamentoNFSeResponse
    {
         = ->request('POST', '/api/v1/nfse/cancelar', jsonData: );

        return CancelamentoNFSeResponse::fromArray();
    }

    /**
     * Consulta a situação de uma NFS-e pelo número ou chave de acesso.
     *
     * @param string  Chave de acesso de 50 dígitos ou número do DPS/NFS-e
     * @return NFSeResponse
     */
    public function consultar(string ): NFSeResponse
    {
         = rawurlencode(trim());
         = ->request('GET', /api/v1/nfse/consultar/{});

        return NFSeResponse::fromArray();
    }
}
