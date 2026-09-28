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

    public function testVerifySignatureAceitaPrefixoSha256DoFormatoLegado(): void
    {
        $legado = '{"evento":"nfe.autorizada","dados":{"cStat":100}}';
        $signature = 'sha256=' . Webhooks::computeSignature($legado, $this->secret);

        $this->assertTrue(Webhooks::verifySignature($legado, $signature, $this->secret));
        $this->assertFalse(Webhooks::verifySignature($legado, 'sha256=' . str_repeat('0', 64), $this->secret));
    }

    public function testConstructEventNormalizaEnvelopeLegado(): void
    {
        $legado = '{"evento":"nfse.autorizada","dados":{"numero_nfse":"202600005001"}}';
        $signature = 'sha256=' . Webhooks::computeSignature($legado, $this->secret);

        $event = Webhooks::constructEvent($legado, $signature, $this->secret);

        $this->assertSame('nfse.autorizada', $event->event);
        $this->assertSame('202600005001', $event->data['numero_nfse']);
    }

    public function testConstructEventConverteTimestampIso8601(): void
    {
        $payload = '{"data":{},"event":"pairus.fiscal.update","timestamp":"2026-09-28T10:00:00-03:00"}';
        $signature = Webhooks::computeSignature($payload, $this->secret);

        $event = Webhooks::constructEvent($payload, $signature, $this->secret);

        $this->assertSame(strtotime('2026-09-28T13:00:00Z'), $event->timestamp);
    }
}
