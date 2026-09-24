<?php

declare(strict_types=1);

namespace Pairus\Tests;

use Pairus\Http\HttpClientInterface;
use Pairus\Http\Response;
use Pairus\PairusClient;
use PHPUnit\Framework\TestCase;

class DFeTest extends TestCase
{
    private array $mockSincronizarData = [
        'sucesso' => true,
        'cstat' => 138,
        'xmotivo' => 'Documento localizado para o destinatário',
        'ult_nsu' => '000000000000100',
        'max_nsu' => '000000000000105',
        'novos_documentos' => 2,
        'mensagem' => 'Sincronização concluída com sucesso.',
        'documentos' => [
            [
                'chave_acesso' => '35260911222333000181550010000015411000015418',
                'nsu' => '000000000000101',
                'numero' => 1541,
                'serie' => 1,
                'cnpj_emitente' => '99888777000166',
                'nome_emitente' => 'Fornecedor Alpha S/A',
                'valor_total' => 250.0,
                'data_emissao' => '2026-09-16T10:00:00-03:00',
                'situacao' => 'autorizada',
                'tipo_documento' => 'procNFe',
                'manifestacao_status' => 'sem_manifestacao',
                'tem_xml_completo' => true,
            ],
        ],
    ];

    private array $mockListarData = [
        'cnpj' => '11222333000181',
        'ambiente' => 'producao',
        'total' => 1,
        'ult_nsu' => '000000000000100',
        'max_nsu' => '000000000000105',
        'documentos' => [
            [
                'chave_acesso' => '35260911222333000181550010000015411000015418',
                'numero' => 1541,
                'valor_total' => 250.0,
            ],
        ],
    ];

    private array $mockManifestarData = [
        'sucesso' => true,
        'cstat' => 135,
        'xmotivo' => 'Evento registrado e vinculado a NF-e',
        'protocolo' => '135260000012345',
        'tipo_evento' => '210210',
    ];

    public function testSincronizar(): void
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                $this->stringContains('/v1/dfe/sincronizar'),
                $this->anything(),
                $this->stringContains('11222333000181')
            )
            ->willReturn(new Response(
                statusCode: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode($this->mockSincronizarData)
            ));

        $client = new PairusClient(apiKey: 'pk_test_123', httpClient: $mockHttp);
        $res = $client->dfe->sincronizar('11222333000181');

        $this->assertTrue($res['sucesso']);
        $this->assertSame(138, $res['cstat']);
        $this->assertSame(2, $res['novos_documentos']);
        $this->assertCount(1, $res['documentos']);
    }

    public function testListarDocumentos(): void
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                $this->stringContains('/v1/dfe/documentos')
            )
            ->willReturn(new Response(
                statusCode: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode($this->mockListarData)
            ));

        $client = new PairusClient(apiKey: 'pk_test_123', httpClient: $mockHttp);
        $res = $client->dfe->listarDocumentos('11222333000181', 'producao', 25);

        $this->assertSame(1, $res['total']);
        $this->assertSame('35260911222333000181550010000015411000015418', $res['documentos'][0]['chave_acesso']);
    }

    public function testManifestar(): void
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                $this->stringContains('/v1/dfe/manifestar'),
                $this->anything(),
                $this->stringContains('210210')
            )
            ->willReturn(new Response(
                statusCode: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode($this->mockManifestarData)
            ));

        $client = new PairusClient(apiKey: 'pk_test_123', httpClient: $mockHttp);
        $res = $client->dfe->manifestar(
            '35260911222333000181550010000015411000015418',
            '11222333000181',
            '210210'
        );

        $this->assertTrue($res['sucesso']);
        $this->assertSame(135, $res['cstat']);
        $this->assertSame('135260000012345', $res['protocolo']);
    }

    public function testBaixarXmlEDanfe(): void
    {
        $mockXml = '<nfeProc><infNFe/></nfeProc>';
        $mockPdf = '%PDF-1.4 mock pdf content';

        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->expects($this->exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls(
                new Response(statusCode: 200, headers: ['Content-Type' => 'application/xml'], body: $mockXml),
                new Response(statusCode: 200, headers: ['Content-Type' => 'application/pdf'], body: $mockPdf)
            );

        $client = new PairusClient(apiKey: 'pk_test_123', httpClient: $mockHttp);

        $xml = $client->dfe->baixarXml('35260911222333000181550010000015411000015418');
        $this->assertSame($mockXml, $xml);

        $pdf = $client->dfe->baixarDanfe('35260911222333000181550010000015411000015418');
        $this->assertSame($mockPdf, $pdf);
    }
}
