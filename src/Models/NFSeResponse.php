<?php

declare(strict_types=1);

namespace Pairus\Models;

/**
 * Resposta oficial da esteira de emissão e simulação de NFS-e (Serviços).
 */
class NFSeResponse
{
    public function __construct(
        public readonly bool $sucesso,
        public readonly string $status,
        public readonly ?string $numeroNfse = null,
        public readonly ?string $codigoVerificacao = null,
        public readonly ?string $chaveAcessoNacional = null,
        public readonly ?string $dataEmissao = null,
        public readonly ?string $linkVisualizacao = null,
        public readonly ?string $danfsePdfBase64 = null,
        public readonly ?string $xmlNfse = null,
        public readonly float $valorServicos = 0.0,
        public readonly float $valorIss = 0.0,
        public readonly float $valorIssRetido = 0.0,
        public readonly float $totalRetencoesFederais = 0.0,
        public readonly float $valorLiquido = 0.0,
        public readonly array $erros = [],
        public readonly array $alertas = [],
        public readonly array $trilhaAuditoria = [],
        public readonly int $creditosCobrados = 0,
        public readonly float $tempoProcessamentoMs = 0.0,
        public readonly array $raw = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sucesso: (bool) ($data['sucesso'] ?? false),
            status: (string) ($data['status'] ?? 'rejeitada'),
            numeroNfse: isset($data['numero_nfse']) ? (string) $data['numero_nfse'] : null,
            codigoVerificacao: isset($data['codigo_verificacao']) ? (string) $data['codigo_verificacao'] : null,
            chaveAcessoNacional: isset($data['chave_acesso_nacional']) ? (string) $data['chave_acesso_nacional'] : null,
            dataEmissao: isset($data['data_emissao']) ? (string) $data['data_emissao'] : null,
            linkVisualizacao: isset($data['link_visualizacao']) ? (string) $data['link_visualizacao'] : null,
            danfsePdfBase64: isset($data['danfse_pdf_base64']) ? (string) $data['danfse_pdf_base64'] : null,
            xmlNfse: isset($data['xml_nfse']) ? (string) $data['xml_nfse'] : null,
            valorServicos: (float) ($data['valor_servicos'] ?? 0.0),
            valorIss: (float) ($data['valor_iss'] ?? 0.0),
            valorIssRetido: (float) ($data['valor_iss_retido'] ?? 0.0),
            totalRetencoesFederais: (float) ($data['total_retencoes_federais'] ?? 0.0),
            valorLiquido: (float) ($data['valor_liquido'] ?? 0.0),
            erros: (array) ($data['erros'] ?? []),
            alertas: (array) ($data['alertas'] ?? []),
            trilhaAuditoria: (array) ($data['trilha_auditoria'] ?? []),
            creditosCobrados: (int) ($data['creditos_cobrados'] ?? 0),
            tempoProcessamentoMs: (float) ($data['tempo_processamento_ms'] ?? 0.0),
            raw: $data
        );
    }
}
