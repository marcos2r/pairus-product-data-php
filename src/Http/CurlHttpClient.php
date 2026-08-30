<?php

declare(strict_types=1);

namespace Pairus\Http;

use Pairus\Exceptions\NetworkException;

/**
 * Cliente HTTP nativo baseado na extensão cURL do PHP.
 * Proporciona zero dependências externas e altíssima performance.
 */
class CurlHttpClient implements HttpClientInterface
{
    public function request(
        string $method,
        string $url,
        array $headers = [],
        ?string $body = null,
        float $timeout = 30.0
    ): Response {
        $ch = curl_init();

        $formattedHeaders = [];
        foreach ($headers as $key => $value) {
            $formattedHeaders[] = "{$key}: {$value}";
        }

        $responseHeaders = [];

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $formattedHeaders,
            CURLOPT_TIMEOUT => (int) ceil($timeout),
            CURLOPT_CONNECTTIMEOUT => (int) ceil($timeout / 2),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HEADERFUNCTION => function ($curl, $header) use (&$responseHeaders) {
                $len = strlen($header);
                $parts = explode(':', $header, 2);
                if (count($parts) === 2) {
                    $responseHeaders[trim($parts[0])] = trim($parts[1]);
                }
                return $len;
            },
        ];

        if ($body !== null && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $options[CURLOPT_POSTFIELDS] = $body;
        }

        curl_setopt_array($ch, $options);

        $responseBody = curl_exec($ch);
        $curlErrorNumber = curl_errno($ch);
        $curlErrorMessage = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($curlErrorNumber !== 0) {
            throw new NetworkException("Falha na comunicação de rede com a API PAIRUS: [{$curlErrorNumber}] {$curlErrorMessage}");
        }

        return new Response(
            statusCode: (int) $statusCode,
            headers: $responseHeaders,
            body: is_string($responseBody) ? $responseBody : ''
        );
    }
}
