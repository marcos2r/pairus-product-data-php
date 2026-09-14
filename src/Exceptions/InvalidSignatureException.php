<?php

declare(strict_types=1);

namespace Pairus\Exceptions;

/**
 * Erro lançado quando a assinatura HMAC de um Webhook é inválida ou expirada.
 */
class InvalidSignatureException extends PairusException
{
}
