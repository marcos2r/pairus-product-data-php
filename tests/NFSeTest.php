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
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.pairus.com.br/v1/nfse/emitir',
                $this->anything(),
                $this->callback(function ($body) {
                    $dados = json_decode($body, true);
                    return isset($dados['prestador']['cnpj']) && $dados['prestador']['cnpj'] === '12345678000199';
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
                    'chave_acesso_nacional' => '35260112345678000199000000000000000000000123',
                    'link_visualizacao' => 'https://www.nfse.gov.br/consultapublica/123',
                    'valor_servicos' => 1500.0,
                    'valor_liquido' => 1500.0
                ])
            ));

        $client = new PairusClient(apiKey: 'pairus_test_key', httpClient: $mockHttp);
        $res = $client->nfse->emitir([
            'prestador' => ['cnpj' => '12345678000199'],
            'tomador' => ['cpf_cnpj' => '98765432000188', 'razao_social' => 'Cliente Teste'],
            'servico' => ['discriminacao' => 'Consultoria de TI', 'valor_servicos' => 1500.0]
        ]);

        $this->assertInstanceOf(NFSeResponse::class, $res);
        $this->assertTrue($res->sucesso);
        $this->assertSame('autorizada', $res->status);
        $this->assertSame('202600000000123', $res->numeroNfse);
        $this->assertSame('35260112345678000199000000000000000000000123', $res->chaveAcessoNacional);
        $this->assertSame('https://www.nfse.gov.br/consultapublica/123', $res->linkVisualizacao);
        $this->assertSame(1500.0, $res->valorLiquido);
    }

    public function testSimularNFSe(): void
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.pairus.com.br/v1/nfse/simular',
                $this->anything(),
                $this->anything(),
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

        $client = new PairusClient(apiKey: 'pairus_test_key', httpClient: $mockHttp);
        $res = $client->nfse->simular([
            'prestador' => ['cnpj' => '12345678000199'],
            'servico' => ['discriminacao' => 'Consultoria', 'valor_servicos' => 1500.0]
        ]);

        $this->assertInstanceOf(NFSeResponse::class, $res);
        $this->assertTrue($res->sucesso);
        $this->assertSame('simulada', $res->status);
    }

    public function testCancelarNFSe(): void
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.pairus.com.br/v1/nfse/cancelar',
                $this->anything(),
                $this->anything(),
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

        $client = new PairusClient(apiKey: 'pairus_test_key', httpClient: $mockHttp);
        $res = $client->nfse->cancelar([
            'numero_nfse' => '202600000000123',
            'chave_acesso_nacional' => '35260112345678000199000000000000000000000123',
            'cnpj_prestador' => '12345678000199',
            'inscricao_municipal' => '87654321',
            'codigo_municipio_ibge' => '3550308',
            'motivo_codigo' => '3',
            'justificativa' => 'Cancelamento por duplicidade de faturamento'
        ]);

        $this->assertInstanceOf(CancelamentoNFSeResponse::class, $res);
        $this->assertTrue($res->sucesso);
        $this->assertSame('202600000000123', $res->numeroNfse);
        $this->assertSame('2026-09-14T15:00:00Z', $res->dataCancelamento);
    }

    public function testConsultarNFSe(): void
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.pairus.com.br/v1/nfse/35260112345678000199000000000000000000000123',
                $this->anything(),
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

        $client = new PairusClient(apiKey: 'pairus_test_key', httpClient: $mockHttp);
        $res = $client->nfse->consultar('35260112345678000199000000000000000000000123');

        $this->assertInstanceOf(NFSeResponse::class, $res);
        $this->assertTrue($res->sucesso);
        $this->assertSame('autorizada', $res->status);
    }
}
