# PAIRUS Product Data SDK para PHP 🐘

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pairus/product-data.svg?style=flat-square)](https://packagist.org/packages/pairus/product-data)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.1-777BB4.svg?style=flat-square)](https://www.php.net/)

SDK oficial da **PAIRUS Soluções Tecnológicas** para PHP moderno (8.1+). Integração nativa de alta performance com o catálogo global de produtos GTIN/EAN, motor preditivo de inteligência artificial para a **Reforma Tributária (IBS, CBS e Imposto Seletivo)**, saneamento fiscal em lote, emissão de **NF-e / NFC-e** com Autocura SEFAZ e **NFS-e Nacional**.

---

## ⚡ Quickstart em 3 Linhas

`ash
composer require pairus/product-data
`

`php
use Pairus\PairusClient;

 = new PairusClient(apiKey: getenv('PAIRUS_API_KEY'));
 = ->products->get('7891000100103');
echo {->xProd} | NCM: {->ncm} | CEST: {->cest};
`

---

## 🧭 Formas de Uso e Flexibilidade

O SDK foi desenhado para respeitar as melhores práticas do PHP moderno (tipagem estrita, DTOs imutáveis public readonly, named arguments e suporte a arrays associativos flexíveis):

`php
// Forma 1: Argumentos nomeados do PHP 8 (alta legibilidade)
 = ->fiscal->predict(
    regimeTributario: 'simples_nacional',
    ufOrigem: 'SP',
    ufDestino: 'RJ',
    xProd: 'Coca-Cola 2L',
    gtin: '7894900010015'
);

// Forma 2: Injeção de dependência em frameworks (Laravel, Symfony)
class FiscalController {
    public function __construct(private PairusClient ) {}
}
`

---

## 📚 Guia de Recursos

### 1. Produtos e Catálogo Global

`php
// Consulta simples por GTIN
 = ->products->get('7891000100103');

// Consulta cadastral enriquecida (SEO, dimensões logísticas, imagem HD)
 = ->products->getEnriched('7891000100103');

// Busca semântica por texto e IA
 = ->products->search('leite condensado sem lactose', limite: 5);
foreach (['produtos'] as ) {
    echo [Score {['score']}] {['descricao']} - GTIN: {['gtin']} . PHP_EOL;
}

// Identificação por imagem fotográfica (OCR + Visão Computacional)
 = ->products->scan('/caminho/foto_embalagem.jpg', tipo: 'gtin');
// Também aceita bytes brutos em memória:
//  = ->products->scan(, nomeArquivo: 'rotulo.png');
`

---

### 2. Predição Fiscal & Reforma Tributária (IBS / CBS / IS)

Calcula automaticamente a matriz tributária completa para 27 UFs e rotas interestaduais:

`php
 = ->fiscal->predict(
    regimeTributario: 'simples_nacional', // 'simples_nacional' | 'lucro_presumido' | 'lucro_real'
    ufOrigem: 'SP',
    ufDestino: 'MG',
    finalidade: 'revenda',               // 'revenda' | 'consumo_final' | 'industrializacao'
    destinatarioContribuinte: true,
    gtin: '7894900010015',
    xProd: 'REFRIGERANTE COCA COLA 2L'
);

 = ->dadosTributarios;
echo NCM: {->ncmSugerido} | CFOP: {->cfop} | CSOSN: {->icmsCstCsosn} . PHP_EOL;
echo IBS/CBS CST: {->ibscbs->cst} | ClassTrib: {->ibscbs->cClassTrib} . PHP_EOL;
echo CBS Efetiva: {->ibscbs->cbsAliquotaEfetiva}% | IBS Efetivo: {->ibscbs->ibsAliquotaEfetiva}% . PHP_EOL;
`

---

### 3. Saneamento Fiscal em Lote

Audite e higienize cadastros inteiros de ERPs e e-commerces em uma única requisição:

`php
 = [
    ['xProd' => 'CERVEJA HEINEKEN LATA 350ML', 'gtin' => '7896045506163', 'ncm' => '00000000', 'uf' => 'SP'],
    ['xProd' => 'ARROZ BRANCO TIPO 1 5KG', 'ncm' => '10063021', 'uf' => 'SP'],
];

 = ->fiscal->sanitize();
foreach (['itens_saneados'] as ) {
    echo {['xProd']} -> NCM: {['ncm_sugerido']} | CEST: {['cest_sugerido']} ({['status']}) . PHP_EOL;
}
`

---

### 4. Emissão de NF-e / NFC-e com Autocura SEFAZ

`php
// Emissão direta com Autocura tributária
 = ->emissao->emitirNfe([
    'natureza_operacao' => 'VENDA MERCADORIA',
    'autocura' => true,
    'itens' => [
        [
            'cProd' => 'SKU-01',
            'xProd' => 'Teclado Mecânico USB',
            'NCM' => '84716052',
            'CFOP' => '5102',
            'uCom' => 'UN',
            'qCom' => 1.0,
            'vUnCom' => 250.0,
            'vProd' => 250.0,
        ]
    ]
]);

echo Status: [{->cStat}] {->xMotivo} . PHP_EOL;
echo DANFE PDF: {->danfeUrl} . PHP_EOL;

// Simulação prévia com CUSTO ZERO de créditos
 = ->emissao->simular([
    'natureza_operacao' => 'TESTE PREVIO',
    'itens' => [['cProd' => 'P1', 'xProd' => 'Item Teste', 'NCM' => '84716052', 'CFOP' => '5102', 'uCom' => 'UN', 'qCom' => 1.0, 'vUnCom' => 10.0, 'vProd' => 10.0]]
]);
`

---

### 5. NFS-e Nacional de Serviços

`php
// 1. Simulação prévia de tributos municipais
 = ->nfse->simular([
    'prestador' => ['cnpj' => '12345678000199'],
    'servico' => ['discriminacao' => 'Desenvolvimento de Software', 'valor_servicos' => 2500.0]
]);

// 2. Emissão definitiva
 = ->nfse->emitir([
    'prestador' => ['cnpj' => '12345678000199'],
    'tomador' => ['cpf_cnpj' => '98765432000188', 'razao_social' => 'Cliente Ltda'],
    'servico' => [
        'discriminacao' => 'Serviços de Programação PHP',
        'valor_servicos' => 2500.0,
        'item_lista_servico' => '01.01',
        'aliquota_iss' => 2.0
    ]
]);

echo NFS-e Nº: {->numeroNfse} | Link PDF: {->linkPdf} . PHP_EOL;

// 3. Consulta de situação
 = ->nfse->consultar(->chaveAcesso);

// 4. Cancelamento homologado
 = ->nfse->cancelar([
    'chave_acesso' => ->chaveAcesso,
    'codigo_cancelamento' => '1',
    'motivo' => 'Cancelamento solicitado pelo cliente'
]);
`

---

### 6. Validação Segura de Webhooks (HMAC-SHA256)

`php
use Pairus\Webhooks;
use Pairus\Exceptions\InvalidSignatureException;

 = file_get_contents('php://input');
 = ['HTTP_X_PAIRUS_SIGNATURE'] ?? '';

try {
     = Webhooks::constructEvent(, , getenv('PAIRUS_WEBHOOK_SECRET'));
    
    if (->event === 'nfe.autorizada') {
        // Processar faturamento concluído
    }
} catch (InvalidSignatureException ) {
    http_response_code(401);
    exit('Assinatura inválida');
}
`

---

## 📋 Tabela de Parâmetros: Obrigatório vs. Opcional

### Predição Fiscal ($pairus->fiscal->predict)

| Parâmetro | Tipo | Obrigatório? | Descrição & Regra Fiscal |
| :--- | :--- | :---: | :--- |
| egimeTributario | string | **Sim** | 'simples_nacional', 'lucro_presumido' ou 'lucro_real'. |
| ufOrigem | string | **Sim** | Sigla da UF do emitente (ex: 'SP'). |
| ufDestino | string | Não | Sigla da UF do destinatário. Se omitido, assume operação interna ($ufOrigem). |
| inalidade | string | Não | 'revenda' (padrão), 'consumo_final' ou 'industrializacao'. |
| destinatarioContribuinte | ool | Não | Define aplicação de DIFAL / ST (padrão: 	rue). |
| gtin | string | Não* | Código de barras GTIN/EAN numérico (8, 12, 13 ou 14 dígitos). |
| xProd | string | Não* | Descrição comercial da mercadoria (para predição por IA/RAG). |
| 
cm | string | Não | NCM atual de 8 dígitos para validação e auditoria. |
| cest | string | Não | Código CEST de 7 dígitos para validação de ICMS-ST. |
| itens | rray | Não | Lista de itens para cálculo consolidado de NF-e completa em lote. |

*\*Nota: Pelo menos um identificador (gtin, xProd ou 
cm) deve ser informado no modo individual.*

---

### NFS-e Nacional ($pairus->nfse->emitir)

| Parâmetro | Tipo | Obrigatório? | Descrição |
| :--- | :--- | :---: | :--- |
| prestador.cnpj | string | **Sim** | CNPJ do emissor prestador de serviços. |
| 	omador.cpf_cnpj | string | **Sim** | CPF ou CNPJ do tomador do serviço. |
| 	omador.razao_social | string | **Sim** | Razão social ou nome completo do tomador. |
| servico.discriminacao | string | **Sim** | Descrição clara dos serviços prestados. |
| servico.valor_servicos | loat | **Sim** | Valor bruto total dos serviços (R$). |
| servico.item_lista_servico | string | Não | Item da LC 116/2003 (ex: '01.01'). |
| servico.codigo_tributacao_municipio | string | Não | Código municipal de tributação da prefeitura. |
| servico.aliquota_iss | loat | Não | Alíquota nominal do ISS (ex: 2.0 para 2%). |
| servico.iss_retido | ool | Não | 	rue se o ISS for retido na fonte pelo tomador. |

---

## 🛠️ Exemplos em Frameworks

### Laravel (Controller & Service)

`php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Pairus\PairusClient;

class FiscalController extends Controller
{
    public function consultar(string , PairusClient )
    {
        try {
             = ->products->get();
            return response()->json();
        } catch (\Pairus\Exceptions\PairusApiException ) {
            return response()->json(['erro' => ->xMotivo], ->statusCode);
        }
    }
}
`

### WordPress / WooCommerce

`php
add_action('woocommerce_process_product_meta', function () {
     = get_post_meta(, '_gtin', true);
    if (!empty()) {
         = new \Pairus\PairusClient(apiKey: defined('PAIRUS_API_KEY') ? PAIRUS_API_KEY : '');
         = ->products->get();
        
        update_post_meta(, '_ncm', ->ncm);
        update_post_meta(, '_cest', ->cest);
    }
});
`

---

## 🛡️ Tratamento de Erros e Padrão SEFAZ

Todas as exceções estendem PairusApiException, expondo os códigos de status e descrições do layout SEFAZ:

`php
try {
    ->products->get('0000000000000');
} catch (\Pairus\Exceptions\AuthenticationException ) {
    // Chave de API inválida ou expirada (HTTP 401)
    echo Erro de autenticação: {->getMessage()};
} catch (\Pairus\Exceptions\RateLimitException ) {
    // Limite de requisições atingido (HTTP 429)
    echo Aguarde {->retryAfter} segundos.;
} catch (\Pairus\Exceptions\PairusApiException ) {
    // Erros de negócio ou rejeição fiscal
    echo SEFAZ cStat: {->cStat} | Motivo: {->xMotivo};
} catch (\Pairus\Exceptions\NetworkException ) {
    // Falha de rede após retentativas exponenciais
    echo Falha de conexão: {->getMessage()};
}
`

---

## 📄 Licença

Distribuído sob a licença MIT. Consulte o arquivo [LICENSE](LICENSE) para mais detalhes.
