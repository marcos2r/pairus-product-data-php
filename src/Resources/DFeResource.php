<?php

declare(strict_types=1);

namespace Pairus\Resources;

/**
 * Namespace de recursos para DFe Inbound, Monitoramento SEFAZ e Manifestação do Destinatário.
 */
class DFeResource extends BaseResource
{
    /**
     * Dispara busca ativa de notas fiscais emitidas contra o CNPJ na SEFAZ Nacional.
     *
     * @param string $cnpj CNPJ da empresa destinatária (14 dígitos)
     * @param string $ambiente 'producao' ou 'homologacao' (padrão: 'producao')
     * @return array Resultado da consulta SEFAZ com novos documentos e controle de NSU
     */
    public function sincronizar(string $cnpj, string $ambiente = 'producao'): array
    {
        return $this->request('POST', '/v1/dfe/sincronizar', jsonData: [
            'cnpj' => $cnpj,
            'ambiente' => $ambiente,
        ]);
    }

    /**
     * Lista notas fiscais de compras emitidas por fornecedores contra a sua empresa.
     *
     * @param string $cnpj CNPJ da empresa destinatária
     * @param string $ambiente 'producao' ou 'homologacao' (padrão: 'producao')
     * @param int $limite Quantidade máxima de registros a retornar (1 a 200)
     * @return array Lista de documentos fiscais capturados e status de manifestação
     */
    public function listarDocumentos(string $cnpj, string $ambiente = 'producao', int $limite = 50): array
    {
        return $this->request('GET', '/v1/dfe/documentos', queryParams: [
            'cnpj' => $cnpj,
            'ambiente' => $ambiente,
            'limite' => $limite,
        ]);
    }

    /**
     * Registra evento oficial de Manifestação do Destinatário perante a SEFAZ.
     *
     * @param string $chaveAcesso Chave de 44 dígitos da NF-e
     * @param string $cnpj CNPJ da empresa destinatária
     * @param string $tipoEvento '210210' (Ciência), '210200' (Confirmação), '210220' (Desconhecimento), '210240' (Não Realizada)
     * @param string|null $justificativa Obrigatória só no '210240' (15 a 255 caracteres); a API a descarta nos demais
     * @param string $ambiente 'producao' ou 'homologacao'
     * @return array Protocolo de homologação do evento na SEFAZ
     */
    public function manifestar(
        string $chaveAcesso,
        string $cnpj,
        string $tipoEvento,
        ?string $justificativa = null,
        string $ambiente = 'producao'
    ): array {
        $payload = [
            'chave_acesso' => $chaveAcesso,
            'cnpj' => $cnpj,
            'tipo_evento' => $tipoEvento,
            'ambiente' => $ambiente,
        ];

        if ($justificativa !== null) {
            $payload['justificativa'] = $justificativa;
        }

        return $this->request('POST', '/v1/dfe/manifestar', jsonData: $payload);
    }

    /**
     * Baixa o arquivo XML autorizado (procNFe) da nota fiscal capturada.
     *
     * @param string $chaveAcesso Chave de 44 dígitos da NF-e
     * @return string Conteúdo do XML em formato string UTF-8
     */
    public function baixarXml(string $chaveAcesso): string
    {
        return $this->requestRaw('GET', "/v1/dfe/xml/{$chaveAcesso}");
    }

    /**
     * Baixa o DANFE em PDF da nota fiscal de fornecedor.
     *
     * @param string $chaveAcesso Chave de 44 dígitos da NF-e
     * @return string Conteúdo binário bruto do arquivo PDF
     */
    public function baixarDanfe(string $chaveAcesso): string
    {
        return $this->requestRaw('GET', "/v1/dfe/danfe/{$chaveAcesso}");
    }

    /**
     * Consulta se há Certificado Digital A1 ativo para o CNPJ e quando ele vence.
     *
     * @param string $cnpj CNPJ do estabelecimento (com ou sem máscara)
     * @return array 'status' => 'ativo' (com 'data_vencimento' e 'dias_restantes') ou 'nao_configurado'
     */
    public function consultarCertificado(string $cnpj): array
    {
        return $this->request('GET', '/v1/dfe/certificado', queryParams: [
            'cnpj' => $cnpj,
        ]);
    }

    /**
     * Instala ou atualiza o Certificado Digital A1 da conta, usado na DF-e e na emissão de NF-e.
     *
     * @param string $alias Apelido da empresa (2 a 120 caracteres)
     * @param string $pfxBase64 Conteúdo do arquivo .pfx codificado em Base64
     * @param string $senha Senha do certificado (só trafega na requisição; não é devolvida)
     * @return array Metadados do certificado validado e instalado (chave 'certificado')
     * @throws \InvalidArgumentException Quando o apelido tem menos de 2 caracteres
     */
    public function enviarCertificado(string $alias, string $pfxBase64, string $senha): array
    {
        if (mb_strlen(trim($alias)) < 2) {
            throw new \InvalidArgumentException('O apelido (alias) do certificado deve ter ao menos 2 caracteres.');
        }

        return $this->request('POST', '/v1/dfe/certificado', jsonData: [
            'alias' => $alias,
            'pfx_base64' => $pfxBase64,
            'senha' => $senha,
        ]);
    }
}
