<?php

declare(strict_types=1);

namespace Pairus\Tests;

use Pairus\Http\HttpClientInterface;
use Pairus\Http\Response;
use Pairus\Models\CancelamentoNFSeResponse;
use Pairus\Models\NFSeResponse;
use Pairus\PairusClient;
use PHPUnit\Framework\TestCase;

class NFSeTest extends TestCase
{
    public function testEmitirNFSe(): void
    {
         = ->createMock(HttpClientInterface::class);
        ->expects(->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.pairus.com.br/api/v1/nfse/emitir',
                ->anything(),
                ->callback(function () {
                     = json_decode(, true);
                    return isset(['prestador']['cnpj']) && ['prestador']['cnpj'] === '12345678000199';
                }),
                30.0
            )
            ->willReturn(new Response(
                statusCode: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode([
                    'sucesso' => true,
                    'status' => 'autorizada',
                    'numero_nfse' => '202600000000123',
                    'chave_acesso' => '35260112345678000199000000000000000000000123',
                    'link_pdf' => 'https://api.pairus.com.br/danfe/nfse/123.pdf',
                    'mensagem' => 'NFS-e emitida com sucesso'
                ])
            ));

         = new PairusClient(apiKey: 'pairus_test_key', httpClient: );
         = ->nfse->emitir([
            'prestador' => ['cnpj' => '12345678000199'],
            'tomador' => ['cpf_cnpj' => '98765432000188', 'razao_social' => 'Cliente Teste'],
            'servico' => ['discriminacao' => 'Consultoria de TI', 'valor_servicos' => 1500.0]
        ]);

        ->assertInstanceOf(NFSeResponse::class, );
        ->assertTrue(->sucesso);
        ->assertSame('autorizada', ->status);
        ->assertSame('202600000000123', ->numeroNfse);
        ->assertSame('35260112345678000199000000000000000000000123', ->chaveAcesso);
        ->assertSame('https://api.pairus.com.br/danfe/nfse/123.pdf', ->linkPdf);
    }

    public function testSimularNFSe(): void
    {
         = ->createMock(HttpClientInterface::class);
        ->expects(->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.pairus.com.br/api/v1/nfse/simular',
                ->anything(),
                ->anything(),
                30.0
            )
            ->willReturn(new Response(
                statusCode: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode([
                    'sucesso' => true,
                    'status' => 'simulada',
                    'mensagem' => 'Simulação calculada com sucesso',
                    'valores' => [
                        'valor_iss' => 75.0,
                        'aliquota_iss' => 5.0
                    ]
                ])
            ));

         = new PairusClient(apiKey: 'pairus_test_key', httpClient: );
         = ->nfse->simular([
            'prestador' => ['cnpj' => '12345678000199'],
            'servico' => ['discriminacao' => 'Consultoria', 'valor_servicos' => 1500.0]
        ]);

        ->assertInstanceOf(NFSeResponse::class, );
        ->assertTrue(->sucesso);
        ->assertSame('simulada', ->status);
    }

    public function testCancelarNFSe(): void
    {
         = ->createMock(HttpClientInterface::class);
        ->expects(->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.pairus.com.br/api/v1/nfse/cancelar',
                ->anything(),
                ->anything(),
                30.0
            )
            ->willReturn(new Response(
                statusCode: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode([
                    'sucesso' => true,
                    'numero_nfse' => '202600000000123',
                    'data_cancelamento' => '2026-09-14T15:00:00Z',
                    'mensagem' => 'Cancelamento homologado'
                ])
            ));

         = new PairusClient(apiKey: 'pairus_test_key', httpClient: );
         = ->nfse->cancelar([
            'chave_acesso' => '35260112345678000199000000000000000000000123',
            'codigo_cancelamento' => '1',
            'motivo' => 'Cancelamento por duplicidade de faturamento'
        ]);

        ->assertInstanceOf(CancelamentoNFSeResponse::class, );
        ->assertTrue(->sucesso);
        ->assertSame('202600000000123', ->numeroNfse);
        ->assertSame('2026-09-14T15:00:00Z', ->dataCancelamento);
    }

    public function testConsultarNFSe(): void
    {
         = ->createMock(HttpClientInterface::class);
        ->expects(->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.pairus.com.br/api/v1/nfse/consultar/35260112345678000199000000000000000000000123',
                ->anything(),
                null,
                30.0
            )
            ->willReturn(new Response(
                statusCode: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode([
                    'sucesso' => true,
                    'status' => 'autorizada',
                    'numero_nfse' => '202600000000123',
                    'mensagem' => 'NFS-e localizada'
                ])
            ));

         = new PairusClient(apiKey: 'pairus_test_key', httpClient: );
         = ->nfse->consultar('35260112345678000199000000000000000000000123');

        ->assertInstanceOf(NFSeResponse::class, );
        ->assertTrue(->sucesso);
        ->assertSame('autorizada', ->status);
    }
}
