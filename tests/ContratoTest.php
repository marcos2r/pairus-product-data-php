<?php

declare(strict_types=1);

namespace Pairus\Tests;

use Pairus\Http\HttpClientInterface;
use Pairus\Http\Response;
use Pairus\PairusClient;
use PHPUnit\Framework\TestCase;

/**
 * Contrato real da API x SDK PHP (fiscal, produtos e NFS-e).
 *
 * As respostas seguem os schemas da API (src/schemas/*), validados contra o SDK Python nos testes
 * tests/test_sdk_python_contrato_*.py. Até a 1.4.0 a predição não lia `cfop_interno`, `is` e
 * `avisos_fiscais`, o produto perdia os dados de `infoAdicional` e o saneamento usava rota inexistente.
 */
class ContratoTest extends TestCase
{
    private function clienteComResposta(array $corpo, ?callable $validarCorpo = null): PairusClient
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->expects($this->once())
            ->method('request')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $validarCorpo !== null ? $this->callback($validarCorpo) : $this->anything(),
                $this->anything()
            )
            ->willReturn(new Response(
                statusCode: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode($corpo)
            ));

        return new PairusClient(apiKey: 'pairus_test_key', httpClient: $mockHttp);
    }

    private function dadosTributariosDaApi(): array
    {
        return [
            'ncm_sugerido' => '22030000', 'cest_sugerido' => '0302100', 'origem_mercadoria' => '0',
            'cfop_interno' => '5405', 'cfop_externo' => '6404',
            'cfop_devolucao_interno' => '1411', 'cfop_devolucao_externo' => '2411',
            'icms_cst_csosn' => '500', 'icms_aliquota' => 0.0, 'icms_reducao_bc' => 0.0, 'icms_sujeito_st' => true,
            'icms_mva_st' => 0.0, 'icms_aliquota_st' => 0.0, 'icms_reducao_bc_st' => 0.0,
            'icms_modalidade_bc' => '3', 'icms_modalidade_bc_st' => null,
            'pis_cst' => '04', 'pis_aliquota' => 0.0, 'cofins_cst' => '04', 'cofins_aliquota' => 0.0,
            'reforma_tributaria_nota' => 'IBS/CBS',
            'ibscbs' => [
                'cst' => '000', 'cClassTrib' => '000001', 'cbs_aliquota' => 0.9, 'cbs_aliquota_efetiva' => 0.9,
                'ibs_aliquota_estadual' => 0.1, 'ibs_aliquota_efetiva' => 0.1, 'aliquota_efetiva_unificada' => 1.0,
            ],
            'is' => ['incidencia' => true, 'CSTIS' => '001', 'cClassTribIS' => '000101', 'pIS' => 0.5, 'nota' => 'Bebida'],
            'motor_ia' => 'fallback_local_heuristico',
            'avisos_fiscais' => ['Produto monofásico: CST 04.'],
        ];
    }

    public function testPredicaoRealPreencheCamposOficiaisELegados(): void
    {
        $client = $this->clienteComResposta(
            ['status' => 'success', 'provider' => 'PAIRUS', 'timestamp' => '2026-09-24 08:00:00',
             'dados_tributarios' => $this->dadosTributariosDaApi()],
            fn($corpo) => json_decode($corpo, true)['destinatario_suframa'] === true
        );

        $res = $client->fiscal->predict(
            regimeTributario: 'simples_nacional',
            ufOrigem: 'SP',
            ufDestino: 'AM',
            ncm: '22030000',
            destinatarioSuframa: true
        );
        $dados = $res->dadosTributarios;

        $this->assertSame('2026-09-24 08:00:00', $res->timestamp);
        $this->assertSame('5405', $dados->cfopInterno);
        $this->assertSame('6404', $dados->cfopExterno);
        $this->assertSame('5405', $dados->cfop);
        $this->assertSame('1411', $dados->cfopDevolucao);
        $this->assertNull($dados->icmsModalidadeBcSt);
        $this->assertSame('001', $dados->impostoSeletivo->cst);
        $this->assertSame(0.5, $dados->impostoSeletivo->aliquota);
        $this->assertTrue($dados->impostoSeletivo->incidencia);
        $this->assertSame(1.0, $dados->aliquotaEfetivaUnificada);
        $this->assertSame(['Produto monofásico: CST 04.'], $dados->avisos);
    }

    public function testProdutoLeInfoAdicional(): void
    {
        $client = $this->clienteComResposta([
            'status' => 'success', 'provider' => 'PAIRUS', 'cStat' => '100',
            'produto' => [
                'GTIN' => '7891000100103', 'tpGTIN' => 'GTIN-13', 'xProd' => 'REFRIGERANTE COLA LATA 350ML',
                'NCM' => '22021000', 'CEST' => '0300700', 'fonte' => 'Cache Firestore', 'atualizado' => true,
                'infoAdicional' => ['xMarca' => 'Marca Cola', 'xCategoria' => 'Bebidas',
                    'urlImagem' => 'https://img.exemplo/cola.jpg', 'pesoBruto' => 0.37, 'pesoLiquido' => 0.35],
            ],
        ]);

        $prod = $client->products->get('7891000100103');

        $this->assertSame('Marca Cola', $prod->marca);
        $this->assertSame('Bebidas', $prod->categoria);
        $this->assertSame('https://img.exemplo/cola.jpg', $prod->imagemUrl);
        $this->assertSame(0.37, $prod->pesoBruto);
        $this->assertSame(0.35, $prod->pesoLiquido);
        $this->assertSame('GTIN-13', $prod->tpGTIN);
    }

    public function testNfseLeDanfseBase64(): void
    {
        $client = $this->clienteComResposta(['sucesso' => true, 'status' => 'autorizada', 'danfse_base64' => 'UERG']);

        $res = $client->nfse->simular(['prestador' => [], 'tomador' => [], 'servico' => []]);

        $this->assertSame('UERG', $res->danfseBase64);
        $this->assertSame('UERG', $res->danfsePdfBase64);
    }
}
