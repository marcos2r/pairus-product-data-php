<?php

declare(strict_types=1);

namespace Pairus\Tests;

use Pairus\Exceptions\AuthenticationException;
use Pairus\PairusClient;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    protected function setUp(): void
    {
        putenv('PAIRUS_API_KEY');
        unset($_ENV['PAIRUS_API_KEY']);
    }

    public function testInstantiateWithExplicitApiKey(): void
    {
        $client = new PairusClient(apiKey: 'pairus_test_key_123');

        $this->assertSame('pairus_test_key_123', $client->config->apiKey);
        $this->assertSame('https://api.pairus.com.br', $client->config->baseUrl);
        $this->assertSame(30.0, $client->config->timeout);
        $this->assertSame(2, $client->config->maxRetries);
    }

    public function testInstantiateWithEnvironmentVariable(): void
    {
        putenv('PAIRUS_API_KEY=pairus_env_key_456');

        $client = new PairusClient();

        $this->assertSame('pairus_env_key_456', $client->config->apiKey);
    }

    public function testThrowsAuthenticationExceptionWhenApiKeyMissing(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionCode(401);

        new PairusClient();
    }
}
