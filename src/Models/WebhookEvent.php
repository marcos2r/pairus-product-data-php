<?php

declare(strict_types=1);

namespace Pairus\Models;

/**
 * Modelo de dados com a carga útil de um evento de Webhook da PAIRUS.
 */
class WebhookEvent
{
    public function __construct(
        public readonly string $event,
        public readonly int $timestamp,
        public readonly array $data,
        public readonly array $raw = []
    ) {}

    /**
     * Aceita o envelope oficial {event, timestamp, data} e o legado dos eventos de emissão
     * {evento, dados}, enviado até 27/12/2026 (sem timestamp: usa o horário de recebimento).
     */
    public static function fromArray(array $data): self
    {
        $dados = $data['data'] ?? $data['dados'] ?? null;

        return new self(
            event: (string) ($data['event'] ?? $data['evento'] ?? 'unknown'),
            timestamp: self::paraUnix($data['timestamp'] ?? null),
            data: is_array($dados) ? $dados : [],
            raw: $data
        );
    }

    /**
     * Converte o timestamp (ISO 8601 enviado pela API ou Unix) em segundos Unix.
     */
    private static function paraUnix(mixed $valor): int
    {
        if (is_int($valor) || (is_string($valor) && ctype_digit($valor))) {
            return (int) $valor;
        }
        if (is_string($valor) && ($unix = strtotime($valor)) !== false) {
            return $unix;
        }

        return time();
    }
}
