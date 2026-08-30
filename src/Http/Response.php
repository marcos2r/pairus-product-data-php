<?php

declare(strict_types=1);

namespace Pairus\Http;

/**
 * Representação encapsulada de uma resposta HTTP.
 */
class Response
{
    public function __construct(
        public readonly int $statusCode,
        public readonly array $headers,
        public readonly string $body
    ) {}

    /**
     * Decodifica o corpo da resposta como array associativo JSON.
     */
    public function json(): array
    {
        if (empty($this->body)) {
            return [];
        }

        $decoded = json_decode($this->body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['detail' => $this->body];
        }

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Retorna o valor de um cabeçalho específico (case-insensitive).
     */
    public function getHeader(string $name): ?string
    {
        $normalized = strtolower($name);
        foreach ($this->headers as $key => $value) {
            if (strtolower((string) $key) === $normalized) {
                return is_array($value) ? implode(', ', $value) : (string) $value;
            }
        }
        return null;
    }
}
