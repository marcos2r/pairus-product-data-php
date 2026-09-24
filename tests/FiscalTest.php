<?php

declare(strict_types=1);

namespace Pairus\Tests;

use Pairus\Http\HttpClientInterface;
use Pairus\Http\Response;
use Pairus\Models\TaxPredictionResponse;
use Pairus\PairusClient;
use PHPUnit\Framework\TestCase;

class FiscalTest extends TestCase
{
    public function testPredictTaxParsesReformaTributariaCorrectly(): void
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->expects($this->once())
            ->method('request')
            ->willReturn(new Response(
                statusCode: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode([
                    'status' => 'sucesso',
                    'provider' => 'pairus_fiscal_engine',
                    'dados_tributarios' => [
                        'ncm_sugerido' => '22021000',
                        'cest_sugerido' => '0300700',
                        'cfop' => '5405',
                        'cfop_devolucao' => '1411',
                        'icms_cst_csosn' => '500',
                        'icms_aliquota' => 0.0,
                        'icms_aliquota_st' => 18.0,
                        'icms_mva_st' => 40.0,
                        'icms_reducao_bc' => 0.0,
                        'icms_reducao_bc_st' => 0.0,
                        'icms_modalidade_bc' => '3',
                        'icms_modalidade_bc_st' => '4',
                        'pis_cst' => '04',
                        'pis_aliquota' => 0.0,
                        'cofins_cst' => '04',
                        'cofins_aliquota' => 0.0,
                        'ipi_cst' => '53',
                        'ipi_aliquota' => 0.0,
                        'ibscbs' => [
                            'cst' => '000',
                            'cClassTrib' => '000001',
                            'cbs_aliquota' => 8.8,
                            'cbs_diferimento' => 0.0,
                            'cbs_reducao_aliquota' => 0.0,
                            'cbs_aliquota_efetiva' => 8.8,
                            'ibs_aliquota_estadual' => 10.0,
                            'ibs_aliquota_municipal' => 7.7,
                            'ibs_diferimento' => 0.0,
                            'ibs_reducao_aliquota' => 0.0,
                            'ibs_aliquota_efetiva' => 17.7
                        ],
                        'imposto_seletivo' => [
                            'cst' => '001',
                            'cClassTribIS' => '000101',
                            'aliquota' => 1.5
                        ],
                        'aliquota_efetiva_unificada' => 26.5,
                        'motor_ia' => 'pairus_fiscal_engine',
                        'avisos' => ['Produto sujeito a Substituição Tributária.']
                    ]
                ])
            ));

        $client = new PairusClient(apiKey: 'pairus_test_key', httpClient: $mockHttp);

        $resultado = $client->fiscal->predict(
            regimeTributario: 'simples_nacional',
            ufOrigem: 'SP',
            ufDestino: 'RJ',
            finalidade: 'revenda',
            gtin: '7894900010015',
            xProd: 'REFRIGERANTE COCA COLA 2L'
        );

        $this->assertInstanceOf(TaxPredictionResponse::class, $resultado);
        $this->assertNotNull($resultado->dadosTributarios);
        $this->assertSame('22021000', $resultado->dadosTributarios->ncmSugerido);
        $this->assertSame('5405', $resultado->dadosTributarios->cfop);
        $this->assertSame('1411', $resultado->dadosTributarios->cfopDevolucao);
        
        // Validação estrita da Reforma Tributária (IBS / CBS)
        $this->assertSame('000', $resultado->dadosTributarios->ibscbs->cst);
        $this->assertSame('000001', $resultado->dadosTributarios->ibscbs->cClassTrib);
        $this->assertSame(8.8, $resultado->dadosTributarios->ibscbs->cbsAliquotaEfetiva);
        $this->assertSame(17.7, $resultado->dadosTributarios->ibscbs->ibsAliquotaEfetiva);

        // Imposto Seletivo
        $this->assertNotNull($resultado->dadosTributarios->impostoSeletivo);
        $this->assertSame('001', $resultado->dadosTributarios->impostoSeletivo->cst);
        $this->assertSame('000101', $resultado->dadosTributarios->impostoSeletivo->cClassTribIS);
        $this->assertSame(1.5, $resultado->dadosTributarios->impostoSeletivo->aliquota);
    }

    public function testSanitizeFiscalItems(): void
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.pairus.com.br/api/v1/fiscal/saneamento',
                $this->anything(),
                $this->callback(function ($body) {
                    $json = json_decode($body, true);
                    return is_array($json) && count($json) === 1 && $json[0]['gtin'] === '7891000100103';
                }),
                30.0
            )
            ->willReturn(new Response(
                statusCode: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode([
                    'total_itens' => 1,
                    'itens_saneados' => [
                        [
                            'gtin' => '7891000100103',
                            'xProd' => 'LEITE CONDENSADO MOCA 395G',
                            'ncm_original' => '00000000',
                            'ncm_sugerido' => '04029900',
                            'cest_sugerido' => '1701500',
                            'status' => 'corrigido'
                        ]
                    ]
                ])
            ));

        $client = new PairusClient(apiKey: 'pairus_test_key', httpClient: $mockHttp);
        $res = $client->fiscal->sanitize([
            [
                'gtin' => '789-1000-10010-3',
                'xProd' => 'LEITE CONDENSADO MOCA 395G',
                'ncm' => '00000000'
            ]
        ]);

        $this->assertIsArray($res);
        $this->assertSame(1, $res['total_itens']);
        $this->assertCount(1, $res['itens_saneados']);
        $this->assertSame('04029900', $res['itens_saneados'][0]['ncm_sugerido']);
    }
}
