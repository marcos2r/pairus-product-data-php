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
     * Cancela a NFS-e registrando o evento e101101 no Sistema Nacional da NFS-e.
     *
     * Sem resposta, repita o pedido: um cancelamento já registrado é confirmado, sem duplicar.
     *
     * @param array $payload 'numero_nfse', 'cnpj_prestador', 'inscricao_municipal', 'codigo_municipio_ibge',
     *                       'justificativa' (15 a 255 caracteres), 'chave_acesso_nacional' e 'motivo_codigo'
     *                       ('1' erro de emissão, '2' serviço não prestado, '3' duplicidade, '9' outros)
     * @return CancelamentoNFSeResponse Resultado, data e XML do evento (xmlEvento)
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
     * @param bool $sincronizar Confere no Sistema Nacional se a nota foi cancelada fora da plataforma
     * @return NFSeResponse
     */
    public function consultar(string $chaveOuNumero, bool $sincronizar = false): NFSeResponse
    {
        $chaveLimpa = rawurlencode(trim($chaveOuNumero));
        $data = $this->request('GET', "/v1/nfse/{$chaveLimpa}", queryParams: $sincronizar ? ['sincronizar' => 'true'] : []);

        return NFSeResponse::fromArray($data);
    }
}
