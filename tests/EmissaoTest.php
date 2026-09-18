<?php

declare(strict_types=1);

namespace Pairus\Tests;

use Pairus\Http\HttpClientInterface;
use Pairus\Http\Response;
use Pairus\PairusClient;
use PHPUnit\Framework\TestCase;

class EmissaoTest extends TestCase
{
    private array $mockEmissaoData = [
        'status' => 'success',
        'cStat' => '100',
        'xMotivo' => 'Autorizado o uso da NF-e',
        'chave_acesso' => '35260900000000000000550010000000011000000018',
        'numero' => 1,
        'serie' => 1,
        'protocolo' => '135260000000001',
        'xml_autorizado' => '<nfeProc>...</nfeProc>',
        'danfe_url' => 'https://api.pairus.com.br/v1/nfe/danfe/35260900000000000000550010000000011000000018',
        'ambiente' => 'homologacao',
        'autocura_aplicada' => false,
    ];

    private array $mockEventoData = [
        'status' => 'success',
        'sucesso' => true,
        'cStat' => '135',
        'xMotivo' => 'Evento registrado e vinculado a NF-e',
        'tipo_evento' => 'CANCELAMENTO',
        'protocolo' => '135260000000002',
        'chave_acesso' => '35260900000000000000550010000000011000000018',
    ];

    public function testEmitirNfe(): void
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->expects($this->once())
            ->method('request')
            ->willReturn(new Response(
                statusCode: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode($this->mockEmissaoData)
            ));

        $client = new PairusClient(apiKey: 'pk_test_123', httpClient: $mockHttp);
        $res = $client->emissao->emitirNfe([
            'natureza_operacao' => 'VENDA DE MERCADORIAS',
            'itens' => [
                [
                    'cProd' => 'PROD001',
                    'xProd' => 'Refrigerante Cola 350ml',
                    'NCM' => '22021000',
                    'CFOP' => '5102',
                    'uCom' => 'UN',
                    'qCom' => 10.0,
                    'vUnCom' => 5.0,
                    'vProd' => 50.0,
                ],
            ],
            'autocura' => true,
        ]);

        $this->assertSame('success', $res->status);
        $this->assertSame('100', $res->cStat);
        $this->assertSame('35260900000000000000550010000000011000000018', $res->chaveAcesso);
        $this->assertSame('https://api.pairus.com.br/v1/nfe/danfe/35260900000000000000550010000000011000000018', $res->danfeUrl);
    }

    public function testEmitirNfce(): void
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->expects($this->once())
            ->method('request')
            ->willReturn(new Response(
                statusCode: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode($this->mockEmissaoData)
            ));

        $client = new PairusClient(apiKey: 'pk_test_123', httpClient: $mockHttp);
        $res = $client->emissao->emitirNfce([
            'natureza_operacao' => 'VENDA CONSUMIDOR FINAL',
            'itens' => [
                [
                    'cProd' => 'PROD002',
                    'xProd' => 'Agua Mineral 500ml',
                    'NCM' => '22011000',
                    'CFOP' => '5102',
                    'uCom' => 'UN',
                    'qCom' => 2.0,
                    'vUnCom' => 3.0,
                    'vProd' => 6.0,
                ],
            ],
        ]);

        $this->assertSame('success', $res->status);
        $this->assertSame('100', $res->cStat);
    }

    public function testSimular(): void
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $simulacao = $this->mockEmissaoData;
        $simulacao['simulacao'] = true;

        $mockHttp->expects($this->once())
            ->method('request')
            ->willReturn(new Response(
                statusCode: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode($simulacao)
            ));

        $client = new PairusClient(apiKey: 'pk_test_123', httpClient: $mockHttp);
        $res = $client->emissao->simular([
            'natureza_operacao' => 'SIMULACAO TESTE',
            'itens' => [['cProd' => 'P1', 'xProd' => 'Item', 'NCM' => '22021000', 'CFOP' => '5102', 'uCom' => 'UN', 'qCom' => 1.0, 'vUnCom' => 10.0, 'vProd' => 10.0]],
        ]);

        $this->assertSame('success', $res->status);
        $this->assertTrue($res->simulacao);
    }

    public function testEventos(): void
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->expects($this->exactly(3))
            ->method('request')
            ->willReturnOnConsecutiveCalls(
                new Response(
                    statusCode: 200,
                    headers: ['Content-Type' => 'application/json'],
                    body: json_encode($this->mockEventoData)
                ),
                new Response(
                    statusCode: 200,
                    headers: ['Content-Type' => 'application/json'],
                    body: json_encode(array_merge($this->mockEventoData, ['tipo_evento' => 'CARTA_CORRECAO']))
                ),
                new Response(
                    statusCode: 200,
                    headers: ['Content-Type' => 'application/json'],
                    body: json_encode([
                        'status' => 'success',
                        'sucesso' => true,
                        'cStat' => '102',
                        'xMotivo' => 'Inutilizacao de numero homologado',
                        'tipo_evento' => 'INUTILIZACAO',
                        'protocolo' => '135260000000003',
                    ])
                )
            );

        $client = new PairusClient(apiKey: 'pk_test_123', httpClient: $mockHttp);

        // Cancelamento
        $resCanc = $client->emissao->cancelar(
            chaveAcesso: '35260900000000000000550010000000011000000018',
            justificativa: 'Cancelamento efetuado dentro do prazo legal'
        );
        $this->assertTrue($resCanc->sucesso);
        $this->assertSame('135', $resCanc->cStat);

        // CC-e
        $resCce = $client->emissao->cartaCorrecao(
            chaveAcesso: '35260900000000000000550010000000011000000018',
            correcao: 'Correcao de dados complementares de expedicao'
        );
        $this->assertSame('CARTA_CORRECAO', $resCce->tipoEvento);

        // Inutilização
        $resInut = $client->emissao->inutilizar(
            cnpjEmitente: '12345678000195',
            serie: 1,
            numeroInicial: 10,
            numeroFinal: 15,
            justificativa: 'Salto involuntario de numeracao fiscal',
            ano: 26,
            modelo: 55
        );
        $this->assertSame('102', $resInut->cStat);
    }
}
