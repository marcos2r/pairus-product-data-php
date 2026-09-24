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
                'https://api.pairus.com.br/v1/gtin/7891000100103',
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

    public function testGetEnrichedProduct(): void
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.pairus.com.br/v2/gtin/7891000100103',
                $this->anything(),
                null,
                30.0
            )
            ->willReturn(new Response(
                statusCode: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode([
                    'gtin' => '7891000100103',
                    'descricao' => 'LEITE CONDENSADO MOCA 395G',
                    'atributos' => ['sabor' => 'Tradicional'],
                    'imagem_url' => 'https://img.pairus.com.br/7891000100103.jpg'
                ])
            ));

        $client = new PairusClient(apiKey: 'pairus_test_key', httpClient: $mockHttp);
        $res = $client->products->getEnriched('7891000100103');

        $this->assertIsArray($res);
        $this->assertSame('7891000100103', $res['gtin']);
        $this->assertSame('LEITE CONDENSADO MOCA 395G', $res['descricao']);
    }

    public function testSearchProducts(): void
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.pairus.com.br/v2/gtin/search?q=leite+condensado',
                $this->anything(),
                null,
                30.0
            )
            ->willReturn(new Response(
                statusCode: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode([
                    'total' => 1,
                    'produtos' => [
                        [
                            'gtin' => '7891000100103',
                            'descricao' => 'LEITE CONDENSADO MOCA 395G',
                            'score' => 0.98
                        ]
                    ]
                ])
            ));

        $client = new PairusClient(apiKey: 'pairus_test_key', httpClient: $mockHttp);
        $res = $client->products->search('leite condensado', 5);

        $this->assertIsArray($res);
        $this->assertSame(1, $res['total']);
        $this->assertCount(1, $res['produtos']);
        $this->assertSame('7891000100103', $res['produtos'][0]['gtin']);
    }

    public function testScanImageProduct(): void
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.pairus.com.br/v2/gtin/scan',
                $this->anything(),
                $this->callback(function ($body) {
                    return is_array($body) && isset($body['file']);
                }),
                30.0
            )
            ->willReturn(new Response(
                statusCode: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode([
                    'gtin' => '7891000100103',
                    'descricao' => 'LEITE CONDENSADO MOCA 395G',
                    'confianca' => 0.99
                ])
            ));

        $client = new PairusClient(apiKey: 'pairus_test_key', httpClient: $mockHttp);
        $res = $client->products->scan('fake-bytes-image', 'foto.jpg', 'gtin');

        $this->assertIsArray($res);
        $this->assertSame('7891000100103', $res['gtin']);
    }
}
