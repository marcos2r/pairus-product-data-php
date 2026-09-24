<?php

declare(strict_types=1);

namespace Pairus\Models;

/**
 * Resposta oficial da rota de emissão e simulação fiscal (NF-e / NFC-e).
 */
class EmissaoResponse
{
    public function __construct(
        public readonly string $status,
        public readonly string $cStat,
        public readonly string $xMotivo,
        public readonly ?string $chaveAcesso = null,
        public readonly ?int $numero = null,
        public readonly ?int $serie = null,
        public readonly ?string $protocolo = null,
        public readonly ?string $xmlAutorizado = null,
        public readonly ?string $danfeUrl = null,
        public readonly ?string $ambiente = null,
        public readonly bool $autocuraAplicada = false,
        public readonly bool $simulacao = false,
        public readonly array $raw = [],
        public readonly bool $sucesso = false,
        public readonly ?string $statusSefaz = null,
        public readonly bool $repeticaoIdempotente = false,
        public readonly ?string $xmlAutorizadoBase64 = null,
        public readonly ?string $danfePdfBase64 = null,
        public readonly ?string $qrcodeUrl = null,
        public readonly int $consumoCreditos = 0
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: (string) ($data['status'] ?? strtolower((string) ($data['status_sefaz'] ?? 'success'))),
            cStat: (string) ($data['cStat'] ?? ''),
            xMotivo: (string) ($data['xMotivo'] ?? ''),
            chaveAcesso: isset($data['chave_acesso']) ? (string) $data['chave_acesso'] : null,
            numero: isset($data['numero']) ? (int) $data['numero'] : null,
            serie: isset($data['serie']) ? (int) $data['serie'] : null,
            protocolo: isset($data['protocolo']) ? (string) $data['protocolo'] : null,
            xmlAutorizado: isset($data['xml_autorizado']) ? (string) $data['xml_autorizado'] : null,
            danfeUrl: isset($data['danfe_url']) ? (string) $data['danfe_url'] : null,
            ambiente: isset($data['ambiente']) ? (string) $data['ambiente'] : null,
            autocuraAplicada: (bool) ($data['autocura_aplicada'] ?? false),
            simulacao: (bool) ($data['simulacao'] ?? false),
            raw: $data,
            sucesso: (bool) ($data['sucesso'] ?? false),
            statusSefaz: isset($data['status_sefaz']) ? (string) $data['status_sefaz'] : null,
            repeticaoIdempotente: (bool) ($data['repeticao_idempotente'] ?? false),
            xmlAutorizadoBase64: isset($data['xml_autorizado_base64']) ? (string) $data['xml_autorizado_base64'] : null,
            danfePdfBase64: isset($data['danfe_pdf_base64']) ? (string) $data['danfe_pdf_base64'] : null,
            qrcodeUrl: isset($data['qrcode_url']) ? (string) $data['qrcode_url'] : null,
            consumoCreditos: (int) ($data['consumo_creditos'] ?? 0)
        );
    }
}
