<?php

declare(strict_types=1);

namespace Pairus\Resources;

/**
 * Namespace de recursos do cadastro de empresas emitentes (CSC da NFC-e).
 */
class EmpresasResource extends BaseResource
{
    /**
     * Grava o CSC da NFC-e do estabelecimento (gerado no portal da SEFAZ da UF).
     * O estabelecimento precisa de certificado próprio na conta; o código nunca é devolvido.
     *
     * @param string $cnpj CNPJ do estabelecimento
     * @param string $ambiente 'producao' ou 'homologacao' (cada ambiente tem o seu CSC)
     * @param string $cscId Identificador do CSC (ex: '000001')
     * @param string $cscCodigo Código do CSC (16 a 36 letras e números)
     * @return array Situação do CSC nos dois ambientes ('producao' e 'homologacao', com 'configurado' e 'csc_id')
     */
    public function configurarCsc(string $cnpj, string $ambiente, string $cscId, string $cscCodigo): array
    {
        return $this->request('PUT', "/v1/empresas/{$cnpj}/csc", jsonData: [
            'ambiente' => $ambiente,
            'csc_id' => $cscId,
            'csc_codigo' => $cscCodigo,
        ]);
    }

    /**
     * Indica, por ambiente, se o CSC da NFC-e está configurado (sem devolver o código).
     *
     * @param string $cnpj CNPJ do estabelecimento
     * @return array Situação do CSC nos dois ambientes
     */
    public function consultarCsc(string $cnpj): array
    {
        return $this->request('GET', "/v1/empresas/{$cnpj}/csc");
    }
}
