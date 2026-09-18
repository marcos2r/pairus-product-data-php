<?php

declare(strict_types=1);

namespace Pairus;

use Pairus\Http\CurlHttpClient;
use Pairus\Http\HttpClientInterface;
use Pairus\Resources\DFeResource;
use Pairus\Resources\EmissaoResource;
use Pairus\Resources\FiscalResource;
use Pairus\Resources\NFSeResource;
use Pairus\Resources\ProductsResource;

/**
 * Cliente oficial em PHP para o ecossistema PAIRUS Product Data.
 */
class PairusClient
{
    public readonly Config $config;
    public readonly ProductsResource $products;
    public readonly FiscalResource $fiscal;
    public readonly EmissaoResource $emissao;
    public readonly NFSeResource $nfse;
    public readonly DFeResource $dfe;

    /**
     * Instancia o cliente da PAIRUS.
     *
     * @param string|null $apiKey Chave da API (se nula, busca na variável de ambiente PAIRUS_API_KEY)
     * @param string|null $baseUrl URL base opcional (padrão: https://api.pairus.com.br)
     * @param float|null $timeout Timeout das requisições em segundos (padrão: 30s)
     * @param int|null $maxRetries Número de retentativas automáticas em falhas de rede (padrão: 2)
     * @param HttpClientInterface|null $httpClient Cliente HTTP customizado (opcional)
     */
    public function __construct(
        ?string $apiKey = null,
        ?string $baseUrl = null,
        ?float $timeout = null,
        ?int $maxRetries = null,
        ?HttpClientInterface $httpClient = null
    ) {
        $this->config = Config::fromEnv(
            apiKey: $apiKey,
            baseUrl: $baseUrl,
            timeout: $timeout,
            maxRetries: $maxRetries
        );

        $client = $httpClient ?? new CurlHttpClient();

        $this->products = new ProductsResource($this->config, $client);
        $this->fiscal = new FiscalResource($this->config, $client);
        $this->emissao = new EmissaoResource($this->config, $client);
        $this->nfse = new NFSeResource($this->config, $client);
        $this->dfe = new DFeResource($this->config, $client);
    }

    /**
     * Atalho estático para o utilitário de Webhooks.
     */
    public static function webhooks(): string
    {
        return Webhooks::class;
    }
}
