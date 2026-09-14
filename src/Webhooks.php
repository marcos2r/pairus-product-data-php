<?php

declare(strict_types=1);

namespace Pairus;

use Pairus\Exceptions\InvalidSignatureException;
use Pairus\Models\WebhookEvent;

/**
 * Utilitário estático oficial para validação criptográfica de Webhooks HMAC-SHA256.
 */
class Webhooks
{
    /**
     * Calcula a assinatura HMAC-SHA256 esperada para o payload informado.
     */
    public static function computeSignature(string $payload, string $secret): string
    {
        return hash_hmac('sha256', $payload, $secret);
    }

    /**
     * Verifica de forma segura (resistente a timing attacks via hash_equals) a assinatura do webhook.
     *
     * @param string|array $payload Corpo bruto da requisição (JSON string) ou array decodificado
     * @param string|null $signature Valor do cabeçalho 'X-Pairus-Signature'
     * @param string $secret Segredo do webhook (webhook_secret) configurado no painel PAIRUS
     * @return bool True se a assinatura for autêntica e válida
     */
    public static function verifySignature(
        string|array $payload,
        ?string $signature,
        string $secret
    ): bool {
        if (empty($signature) || empty($secret)) {
            return false;
        }

        $payloadStr = is_array($payload)
            ? json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            : $payload;

        if ($payloadStr === false || $payloadStr === '') {
            return false;
        }

        $expectedSignature = self::computeSignature($payloadStr, $secret);

        return hash_equals(trim($expectedSignature), trim($signature));
    }

    /**
     * Valida a assinatura criptográfica e converte o payload em um modelo WebhookEvent tipado.
     *
     * @throws InvalidSignatureException Se a assinatura for inválida ou manipulada
     * @throws \JsonException Se o corpo do JSON estiver corrompido
     */
    public static function constructEvent(
        string $payload,
        ?string $signature,
        string $secret
    ): WebhookEvent {
        if (!self::verifySignature($payload, $signature, $secret)) {
            throw new InvalidSignatureException("A assinatura do webhook ('X-Pairus-Signature') é inválida ou expirada.");
        }

        $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        return WebhookEvent::fromArray($data);
    }
}
