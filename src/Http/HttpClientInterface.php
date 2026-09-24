<?php

declare(strict_types=1);

namespace Pairus\Http;

use Pairus\Exceptions\NetworkException;

/**
 * Contrato agnóstico para clientes de transporte HTTP do SDK PAIRUS.
 */
interface HttpClientInterface
{
    /**
     * Executa uma requisição HTTP.
     *
     * @param string $method Método HTTP (GET, POST, etc.)
     * @param string $url URL completa de destino
     * @param array $headers Cabeçalhos da requisição
     * @param string|null $body Corpo bruto (ex: payload JSON)
     * @param float $timeout Timeout em segundos
     * @return Response
     * @throws NetworkException Em caso de erro de conexão ou timeout
     */
    public function request(
        string $method,
        string $url,
        array $headers = [],
        string|array|null $body = null,
        float $timeout = 30.0
    ): Response;
}
