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

    public static function fromArray(array $data): self
    {
        return new self(
            event: (string) ($data['event'] ?? 'unknown'),
            timestamp: (int) ($data['timestamp'] ?? time()),
            data: is_array($data['data'] ?? null) ? $data['data'] : [],
            raw: $data
        );
    }
}
