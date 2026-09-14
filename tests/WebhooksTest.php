<?php

declare(strict_types=1);

namespace Pairus\Tests;

use Pairus\Exceptions\InvalidSignatureException;
use Pairus\Models\WebhookEvent;
use Pairus\Webhooks;
use PHPUnit\Framework\TestCase;

class WebhooksTest extends TestCase
{
    private string $secret = 'whsec_teste_segredo_12345';
    private string $payload = '{"event":"product.updated","timestamp":1700000000,"data":{"gtin":"7891000100103"}}';

    public function testComputeSignature(): void
    {
        $signature = Webhooks::computeSignature($this->payload, $this->secret);

        $this->assertNotEmpty($signature);
        $this->assertSame(64, strlen($signature));
    }

    public function testVerifySignatureSuccess(): void
    {
        $signature = Webhooks::computeSignature($this->payload, $this->secret);

        $this->assertTrue(Webhooks::verifySignature($this->payload, $signature, $this->secret));
    }

    public function testVerifySignatureFailsOnTamperedPayload(): void
    {
        $signature = Webhooks::computeSignature($this->payload, $this->secret);
        $tamperedPayload = '{"event":"product.updated","timestamp":1700000000,"data":{"gtin":"0000000000000"}}';

        $this->assertFalse(Webhooks::verifySignature($tamperedPayload, $signature, $this->secret));
    }

    public function testConstructEventSuccess(): void
    {
        $signature = Webhooks::computeSignature($this->payload, $this->secret);
        $event = Webhooks::constructEvent($this->payload, $signature, $this->secret);

        $this->assertInstanceOf(WebhookEvent::class, $event);
        $this->assertSame('product.updated', $event->event);
        $this->assertSame(1700000000, $event->timestamp);
        $this->assertSame('7891000100103', $event->data['gtin']);
    }

    public function testConstructEventThrowsOnInvalidSignature(): void
    {
        $this->expectException(InvalidSignatureException::class);

        Webhooks::constructEvent($this->payload, 'invalid_signature_hex', $this->secret);
    }
}
