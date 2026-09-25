<?php

declare(strict_types=1);

namespace Pairus;

use Pairus\Exceptions\AuthenticationException;

/**
 * Objeto de configuração do cliente PAIRUS.
 */
class Config
{
    public const DEFAULT_BASE_URL = 'https://api.pairus.com.br';
    public const DEFAULT_TIMEOUT = 30.0;
    public const DEFAULT_MAX_RETRIES = 2;

    public function __construct(
        public readonly string $apiKey,
        public readonly string $baseUrl = self::DEFAULT_BASE_URL,
        public readonly float $timeout = self::DEFAULT_TIMEOUT,
        public readonly int $maxRetries = self::DEFAULT_MAX_RETRIES
    ) {}

    /**
     * Instancia a configuração a partir de parâmetros explícitos ou variáveis de ambiente.
     */
    public static function fromEnv(
        ?string $apiKey = null,
        ?string $baseUrl = null,
        ?float $timeout = null,
        ?int $maxRetries = null
    ): self {
        $resolvedApiKey = $apiKey ?? getenv('PAIRUS_API_KEY') ?: (isset($_ENV['PAIRUS_API_KEY']) ? (string) $_ENV['PAIRUS_API_KEY'] : null);
        $resolvedBaseUrl = $baseUrl ?? getenv('PAIRUS_BASE_URL') ?: (isset($_ENV['PAIRUS_BASE_URL']) ? (string) $_ENV['PAIRUS_BASE_URL'] : self::DEFAULT_BASE_URL);
        
        $envTimeout = getenv('PAIRUS_TIMEOUT') ?: (isset($_ENV['PAIRUS_TIMEOUT']) ? (string) $_ENV['PAIRUS_TIMEOUT'] : null);
        $resolvedTimeout = $timeout ?? ($envTimeout !== null ? (float) $envTimeout : self::DEFAULT_TIMEOUT);

        $envRetries = getenv('PAIRUS_MAX_RETRIES') ?: (isset($_ENV['PAIRUS_MAX_RETRIES']) ? (string) $_ENV['PAIRUS_MAX_RETRIES'] : null);
        $resolvedRetries = $maxRetries ?? ($envRetries !== null ? (int) $envRetries : self::DEFAULT_MAX_RETRIES);

        if (empty($resolvedApiKey)) {
            throw new AuthenticationException(
                statusCode: 401,
                cStat: "401",
                xMotivo: "A chave da API PAIRUS é obrigatória. Forneça o parâmetro 'api_key' ou defina a variável de ambiente PAIRUS_API_KEY."
            );
        }

        return new self(
            apiKey: trim($resolvedApiKey),
            baseUrl: rtrim($resolvedBaseUrl, '/'),
            timeout: $resolvedTimeout,
            maxRetries: max(0, $resolvedRetries)
        );
    }
}
