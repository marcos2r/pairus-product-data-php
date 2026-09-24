<?php

declare(strict_types=1);

namespace Pairus\Resources;

use Pairus\Config;
use Pairus\Exceptions\AuthenticationException;
use Pairus\Exceptions\NetworkException;
use Pairus\Exceptions\PairusApiException;
use Pairus\Exceptions\RateLimitException;
use Pairus\Http\HttpClientInterface;
use Pairus\Http\Response;

/**
 * Classe base para todos os recursos da API PAIRUS.
 */
abstract class BaseResource
{
    public function __construct(
        protected readonly Config $config,
        protected readonly HttpClientInterface $httpClient
    ) {}

    /**
     * Executa uma requisição HTTP à API com retentativas automáticas exponenciais.
     *
     * @throws PairusApiException
     * @throws AuthenticationException
     * @throws RateLimitException
     * @throws NetworkException
     */
    protected function request(
        string $method,
        string $path,
        array $queryParams = [],
        ?array $jsonData = null,
        ?array $files = null,
        array $formData = [],
        array $extraHeaders = []
    ): array {
        $url = $this->config->baseUrl . '/' . ltrim($path, '/');
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        $headers = [
            'Authorization' => "Bearer {$this->config->apiKey}",
            'x-api-key' => $this->config->apiKey,
            'Accept' => 'application/json',
            'User-Agent' => 'Pairus-PHP-SDK/1.5.0 (PHP/' . PHP_VERSION . ')',
        ] + $extraHeaders;

        $body = null;
        if ($files !== null) {
            $body = array_merge($formData, $files);
        } elseif ($jsonData !== null) {
            $headers['Content-Type'] = 'application/json';
            $body = json_encode($jsonData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $attempts = 0;
        $maxAttempts = $this->config->maxRetries + 1;
        $lastException = null;

        while ($attempts < $maxAttempts) {
            $attempts++;
            try {
                $response = $this->httpClient->request(
                    method: $method,
                    url: $url,
                    headers: $headers,
                    body: $body,
                    timeout: $this->config->timeout
                );

                return $this->handleResponse($response);
            } catch (NetworkException $e) {
                $lastException = $e;
                if ($attempts >= $maxAttempts) {
                    throw $e;
                }
                // Backoff exponencial simples
                usleep((int) (pow(2, $attempts) * 250000));
            } catch (PairusApiException $e) {
                // Mesma venda ainda em processamento (409 com Idempotency-Key): repete com a mesma chave
                if ($e->statusCode !== 409 || !isset($headers['Idempotency-Key']) || $attempts >= $maxAttempts) {
                    throw $e;
                }
                $lastException = $e;
                usleep((int) (pow(2, $attempts) * 250000));
            }
        }

        throw $lastException ?? new NetworkException("Falha desconhecida na requisição.");
    }

    /**
     * Executa uma requisição HTTP à API retornando o conteúdo bruto (XML ou PDF binário).
     *
     * @throws PairusApiException
     * @throws AuthenticationException
     * @throws RateLimitException
     * @throws NetworkException
     */
    protected function requestRaw(
        string $method,
        string $path,
        array $queryParams = []
    ): string {
        $url = $this->config->baseUrl . '/' . ltrim($path, '/');
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        $headers = [
            'Authorization' => "Bearer {$this->config->apiKey}",
            'x-api-key' => $this->config->apiKey,
            'Accept' => '*/*',
            'User-Agent' => 'Pairus-PHP-SDK/1.5.0 (PHP/' . PHP_VERSION . ')',
        ];

        $attempts = 0;
        $maxAttempts = $this->config->maxRetries + 1;
        $lastException = null;

        while ($attempts < $maxAttempts) {
            $attempts++;
            try {
                $response = $this->httpClient->request(
                    method: $method,
                    url: $url,
                    headers: $headers,
                    body: null,
                    timeout: $this->config->timeout
                );

                if ($response->statusCode >= 200 && $response->statusCode < 300) {
                    return $response->body;
                }

                $this->handleResponse($response);
            } catch (NetworkException $e) {
                $lastException = $e;
                if ($attempts >= $maxAttempts) {
                    throw $e;
                }
                usleep((int) (pow(2, $attempts) * 250000));
            }
        }

        throw $lastException ?? new NetworkException("Falha desconhecida na requisição.");
    }

    /**
     * Processa a resposta HTTP e converte erros em exceções tipadas.
     */
    protected function handleResponse(Response $response): array
    {
        $status = $response->statusCode;
        $data = $response->json();

        if ($status >= 200 && $status < 300) {
            return $data;
        }

        $cStat = isset($data['cStat']) ? (string) $data['cStat'] : (string) $status;
        $xMotivo = isset($data['xMotivo']) ? (string) $data['xMotivo'] : ($data['detail'] ?? "Erro HTTP {$status}");
        $detail = isset($data['detail']) ? (string) $data['detail'] : null;

        if ($status === 401) {
            throw new AuthenticationException(
                statusCode: 401,
                cStat: $cStat,
                xMotivo: $xMotivo,
                detail: $detail,
                rawResponse: $data
            );
        }

        if ($status === 429) {
            $retryAfterHeader = $response->getHeader('retry-after');
            $retryAfter = $retryAfterHeader !== null ? (float) $retryAfterHeader : null;

            throw new RateLimitException(
                statusCode: 429,
                retryAfter: $retryAfter,
                cStat: $cStat,
                xMotivo: $xMotivo,
                detail: $detail,
                rawResponse: $data
            );
        }

        throw new PairusApiException(
            statusCode: $status,
            cStat: $cStat,
            xMotivo: $xMotivo,
            detail: $detail,
            rawResponse: $data
        );
    }
}
