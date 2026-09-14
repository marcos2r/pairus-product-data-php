<?php

declare(strict_types=1);

namespace Pairus\Tests;

use Pairus\Http\HttpClientInterface;
use Pairus\Http\Response;
use Pairus\Models\ProductDetails;
use Pairus\PairusClient;
use PHPUnit\Framework\TestCase;

class ProductsTest extends TestCase
{
    public function testGetProductParsesCorrectly(): void
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.pairus.com.br/api/produtos/7891000100103',
                $this->anything(),
                null,
                30.0
            )
            ->willReturn(new Response(
                statusCode: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode([
                    'status' => 'sucesso',
                    'produto' => [
                        'GTIN' => '7891000100103',
                        'xProd' => 'LEITE CONDENSADO MOCA 395G',
                        'NCM' => '04029900',
                        'CEST' => '1701500',
                        'marca' => 'NESTLE',
                        'fabricante' => 'NESTLE BRASIL LTDA',
                        'categoria' => 'Laticínios',
                        'peso_bruto' => 0.395,
                        'quantidade_embalagem' => 1
                    ]
                ])
            ));

        $client = new PairusClient(
            apiKey: 'pairus_test_key',
            httpClient: $mockHttp
        );

        $produto = $client->products->get('789-1000-10010-3');

        $this->assertInstanceOf(ProductDetails::class, $produto);
        $this->assertSame('7891000100103', $produto->gtin);
        $this->assertSame('LEITE CONDENSADO MOCA 395G', $produto->xProd);
        $this->assertSame('04029900', $produto->ncm);
        $this->assertSame('1701500', $produto->cest);
        $this->assertSame('NESTLE', $produto->marca);
        $this->assertSame(0.395, $produto->pesoBruto);
    }
}
