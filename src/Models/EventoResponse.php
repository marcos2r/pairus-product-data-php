<?php

declare(strict_types=1);

namespace Pairus\Models;

/**
 * Resposta oficial da rota de eventos e inutilização SEFAZ.
 */
class EventoResponse
{
    public function __construct(
        public readonly string $status,
        public readonly bool $sucesso,
        public readonly string $cStat,
        public readonly string $xMotivo,
        public readonly string $tipoEvento,
        public readonly ?string $protocolo = null,
        public readonly ?string $chaveAcesso = null,
        public readonly ?string $dataEvento = null,
        public readonly ?string $xmlEventoBase64 = null,
        public readonly array $raw = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: (string) ($data['status'] ?? 'success'),
            sucesso: (bool) ($data['sucesso'] ?? false),
            cStat: (string) ($data['cStat'] ?? ''),
            xMotivo: (string) ($data['xMotivo'] ?? ''),
            tipoEvento: (string) ($data['tipo_evento'] ?? ''),
            protocolo: isset($data['protocolo_evento']) || isset($data['protocolo'])
                ? (string) ($data['protocolo_evento'] ?? $data['protocolo'])
                : null,
            chaveAcesso: isset($data['chave_acesso']) ? (string) $data['chave_acesso'] : null,
            dataEvento: isset($data['data_registro']) || isset($data['data_evento'])
                ? (string) ($data['data_registro'] ?? $data['data_evento'])
                : null,
            xmlEventoBase64: isset($data['xml_evento_base64']) ? (string) $data['xml_evento_base64'] : null,
            raw: $data
        );
    }
}
