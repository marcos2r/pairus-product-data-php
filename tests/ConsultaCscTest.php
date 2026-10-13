<?php

declare(strict_types=1);

namespace Pairus\Tests;

use Pairus\Http\HttpClientInterface;
use Pairus\Http\Response;
use Pairus\PairusClient;
use PHPUnit\Framework\TestCase;

class ConsultaCscTest extends TestCase
{
    private const CHAVE = '35260911222333000181650010000001231000001234';

    private function resposta(array $corpo): Response
    {
        return new Response(statusCode: 200, headers: ['Content-Type' => 'application/json'], body: json_encode($corpo));
    }

    public function testConsultarNotaEmitidaComXml(): void
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->expects($this->once())
            ->method('request')
            ->with('GET', $this->stringContains('/v1/nfe/' . self::CHAVE . '?incluir_xml=true'))
            ->willReturn($this->resposta(['chave_acesso' => self::CHAVE, 'status_sefaz' => 'AUTORIZADA']));

        $client = new PairusClient(apiKey: 'pk_test_123', httpClient: $mockHttp);
        $nota = $client->emissao->consultar(self::CHAVE, true);

        $this->assertSame('AUTORIZADA', $nota['status_sefaz']);
    }

    public function testBuscarPorReferencia(): void
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->expects($this->once())
            ->method('request')
            ->with('GET', $this->logicalAnd(
                $this->stringContains('cnpj_emitente=11222333000181'),
                $this->stringContains('referencia_externa=pedido-8841')
            ))
            ->willReturn($this->resposta([['chave_acesso' => self::CHAVE, 'numero' => 123]]));

        $client = new PairusClient(apiKey: 'pk_test_123', httpClient: $mockHttp);
        $notas = $client->emissao->buscarPorReferencia('11222333000181', 'pedido-8841');

        $this->assertSame(123, $notas[0]['numero']);
    }

    public function testConfigurarCscEnviaPut(): void
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->expects($this->once())
            ->method('request')
            ->with('PUT', $this->stringContains('/v1/empresas/11222333000181/csc'), $this->anything(), $this->stringContains('A1B2C3D4E5F6G7H8I9J0'))
            ->willReturn($this->resposta([
                'cnpj' => '11222333000181',
                'producao' => ['configurado' => true, 'csc_id' => '000001'],
                'homologacao' => ['configurado' => false, 'csc_id' => null],
            ]));

        $client = new PairusClient(apiKey: 'pk_test_123', httpClient: $mockHttp);
        $status = $client->empresas->configurarCsc('11222333000181', 'producao', '000001', 'A1B2C3D4E5F6G7H8I9J0');

        $this->assertTrue($status['producao']['configurado']);
    }

    public function testEmissaoDevolveReferenciaExterna(): void
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->method('request')->willReturn($this->resposta([
            'sucesso' => true, 'status_sefaz' => 'AUTORIZADA', 'cStat' => 100, 'xMotivo' => 'Autorizado',
            'referencia_externa' => 'pedido-8841',
        ]));

        $client = new PairusClient(apiKey: 'pk_test_123', httpClient: $mockHttp);
        $res = $client->emissao->emitirNfce(['cnpj_emitente' => '11222333000181', 'itens' => []]);

        $this->assertSame('pedido-8841', $res->referenciaExterna);
    }
}
