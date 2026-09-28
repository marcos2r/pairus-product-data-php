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
    /** Prefixo do formato legado dos eventos de emissão de NF-e/NFC-e/NFS-e. */
    public const PREFIXO_ASSINATURA = 'sha256=';

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
     * @param string|null $signature Valor do cabeçalho 'X-Pairus-Signature', com ou sem o prefixo 'sha256='
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

        // Formato legado dos eventos de emissão (aceito até 27/12/2026): 'sha256=<hex>'
        $recebida = strtolower(trim($signature));
        if (str_starts_with($recebida, self::PREFIXO_ASSINATURA)) {
            $recebida = substr($recebida, strlen(self::PREFIXO_ASSINATURA));
        }

        $expectedSignature = self::computeSignature($payloadStr, $secret);

        return hash_equals($expectedSignature, $recebida);
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
