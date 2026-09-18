# PAIRUS Product Data SDK para PHP 🐘

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pairus/product-data.svg?style=flat-square)](https://packagist.org/packages/pairus/product-data)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.1-777BB4.svg?style=flat-square)](https://www.php.net/)

SDK oficial da **PAIRUS Soluções Tecnológicas** para PHP moderno (8.1+). Integração nativa de alta performance com o catálogo global de produtos GTIN/EAN, motor preditivo de inteligência artificial para a **Reforma Tributária (IBS, CBS e Imposto Seletivo)**, saneamento fiscal em lote, emissão de **NF-e / NFC-e** com Autocura SEFAZ e **NFS-e Nacional**.

---

## ⚡ Quickstart em 3 Linhas

```bash
composer require pairus/product-data
```

```php
use Pairus\PairusClient;

$pairus = new PairusClient(apiKey: getenv('PAIRUS_API_KEY'));
$produto = $pairus->products->get('7891000100103');
echo "{$produto->xProd} | NCM: {$produto->ncm} | CEST: {$produto->cest}";
```

---

## 🧭 Formas de Uso e Flexibilidade

O SDK foi desenhado para respeitar as melhores práticas do PHP moderno (tipagem estrita, DTOs imutáveis `public readonly`, named arguments e suporte a arrays associativos flexíveis):

```php
// Forma 1: Argumentos nomeados do PHP 8 (alta legibilidade)
$predicao = $pairus->fiscal->predict(
    regimeTributario: 'simples_nacional',
    ufOrigem: 'SP',
    ufDestino: 'RJ',
    xProd: 'Coca-Cola 2L',
    gtin: '7894900010015'
);

// Forma 2: Injeção de dependência em frameworks (Laravel, Symfony)
class FiscalController {
    public function __construct(private PairusClient $pairus) {}
}
```

---

## 📚 Guia de Recursos

### 1. Produtos e Catálogo Global

```php
// Consulta simples por GTIN
$produto = $pairus->products->get('7891000100103');

// Consulta cadastral enriquecida (SEO, dimensões logísticas, imagem HD)
$produto = $pairus->products->getEnriched('7891000100103');

// Busca semântica por texto e IA
$busca = $pairus->products->search('leite condensado sem lactose', limite: 5);
foreach ($busca['produtos'] as $item) {
    echo "[Score {$item['score']}] {$item['descricao']} - GTIN: {$item['gtin']}" . PHP_EOL;
}

// Identificação por imagem fotográfica (OCR + Visão Computacional)
$scan = $pairus->products->scan('/caminho/foto_embalagem.jpg', tipo: 'gtin');
// Também aceita bytes brutos em memória:
// $scan = $pairus->products->scan($bytesFoto, nomeArquivo: 'rotulo.png');
```

---

### 2. Predição Fiscal & Reforma Tributária (IBS / CBS / IS)

Calcula automaticamente a matriz tributária completa para 27 UFs e rotas interestaduais:

```php
$predicao = $pairus->fiscal->predict(
    regimeTributario: 'simples_nacional', // 'simples_nacional' | 'lucro_presumido' | 'lucro_real'
    ufOrigem: 'SP',
    ufDestino: 'MG',
    finalidade: 'revenda',               // 'revenda' | 'consumo_final' | 'industrializacao'
    destinatarioContribuinte: true,
    gtin: '7894900010015',
    xProd: 'REFRIGERANTE COCA COLA 2L'
);

$trib = $predicao->dadosTributarios;
echo "NCM: {$trib->ncmSugerido} | CFOP: {$trib->cfop} | CSOSN: {$trib->icmsCstCsosn}" . PHP_EOL;
echo "IBS/CBS CST: {$trib->ibscbs->cst} | ClassTrib: {$trib->ibscbs->cClassTrib}" . PHP_EOL;
echo "CBS Efetiva: {$trib->ibscbs->cbsAliquotaEfetiva}% | IBS Efetivo: {$trib->ibscbs->ibsAliquotaEfetiva}%" . PHP_EOL;
```

---

### 3. Saneamento Fiscal em Lote

Audite e higienize cadastros inteiros de ERPs e e-commerces em uma única requisição:

```php
$lote = [
    ['xProd' => 'CERVEJA HEINEKEN LATA 350ML', 'gtin' => '7896045506163', 'ncm' => '00000000', 'uf' => 'SP'],
    ['xProd' => 'ARROZ BRANCO TIPO 1 5KG', 'ncm' => '10063021', 'uf' => 'SP'],
];

$resultado = $pairus->fiscal->sanitize($lote);
foreach ($resultado['itens_saneados'] as $item) {
    echo "{$item['xProd']} -> NCM: {$item['ncm_sugerido']} | CEST: {$item['cest_sugerido']} ({$item['status']})" . PHP_EOL;
}
```

---

### 4. Emissão de NF-e / NFC-e com Autocura SEFAZ

```php
// Emissão direta com Autocura tributária
$nfe = $pairus->emissao->emitirNfe([
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

echo "Status: [{$nfe->cStat}] {$nfe->xMotivo}" . PHP_EOL;
echo "DANFE PDF: {$nfe->danfeUrl}" . PHP_EOL;

// Simulação prévia com CUSTO ZERO de créditos
$simulacao = $pairus->emissao->simular([
    'natureza_operacao' => 'TESTE PREVIO',
    'itens' => [['cProd' => 'P1', 'xProd' => 'Item Teste', 'NCM' => '84716052', 'CFOP' => '5102', 'uCom' => 'UN', 'qCom' => 1.0, 'vUnCom' => 10.0, 'vProd' => 10.0]]
]);
```

---

### 5. NFS-e Nacional de Serviços

```php
// 1. Simulação prévia de tributos municipais
$simulacao = $pairus->nfse->simular([
    'prestador' => ['cnpj' => '12345678000199'],
    'servico' => ['discriminacao' => 'Desenvolvimento de Software', 'valor_servicos' => 2500.0]
]);

// 2. Emissão definitiva
$nfse = $pairus->nfse->emitir([
    'prestador' => ['cnpj' => '12345678000199'],
    'tomador' => ['cpf_cnpj' => '98765432000188', 'razao_social' => 'Cliente Ltda'],
    'servico' => [
        'discriminacao' => 'Serviços de Programação PHP',
        'valor_servicos' => 2500.0,
        'item_lista_servico' => '01.01',
        'aliquota_iss' => 2.0
    ]
]);

echo "NFS-e Nº: {$nfse->numeroNfse} | Link PDF: {$nfse->linkPdf}" . PHP_EOL;

// 3. Consulta de situação
$status = $pairus->nfse->consultar($nfse->chaveAcesso);

// 4. Cancelamento homologado
$cancel = $pairus->nfse->cancelar([
    'chave_acesso' => $nfse->chaveAcesso,
    'codigo_cancelamento' => '1',
    'motivo' => 'Cancelamento solicitado pelo cliente'
]);
```

---

### 6. DF-e Inbound & Captura Ativa SEFAZ (Gestão de Compras)

Capture ativamente as notas fiscais emitidas por fornecedores contra o CNPJ da sua empresa via WebService `NFeDistribuicaoDFe` da SEFAZ Nacional:

```php
// 1. Sincronizar e consultar novos documentos na SEFAZ Nacional
$sync = $pairus->dfe->sincronizar(cnpj: '12345678000195', ambiente: 'producao');

echo "Status SEFAZ [{$sync['cstat']}]: {$sync['xmotivo']}" . PHP_EOL;
echo "Novos documentos capturados: {$sync['novos_documentos']}" . PHP_EOL;
echo "Avanço de NSU: {$sync['ult_nsu']} -> {$sync['max_nsu']}" . PHP_EOL;

foreach ($sync['documentos'] as $doc) {
    echo "NF-e {$doc['numero']}/{$doc['serie']} - R$ {$doc['valor_total']} ({$doc['nome_emitente']})" . PHP_EOL;
}

// 2. Listar notas fiscais de compras capturadas
$compras = $pairus->dfe->listarDocumentos(cnpj: '12345678000195', limite: 50);

// 3. Manifestação do Destinatário perante a SEFAZ
// Eventos: '210210' (Ciência), '210200' (Confirmação), '210220' (Desconhecimento), '210240' (Não Realizada)
$manif = $pairus->dfe->manifestar(
    chaveAcesso: '35260912345678000195550010000000451234567890',
    cnpj: '12345678000195',
    tipoEvento: '210210' // Ciência da Emissão para liberar download do XML completo
);
echo "Manifestação homologada. Protocolo: {$manif['protocolo']}" . PHP_EOL;

// 4. Download do XML autorizado (procNFe) e DANFE em PDF
$chave = '35260912345678000195550010000000451234567890';
$xmlString = $pairus->dfe->baixarXml($chave);
file_put_contents("{$chave}.xml", $xmlString);

$danfePdfBytes = $pairus->dfe->baixarDanfe($chave);
file_put_contents("DANFE_{$chave}.pdf", $danfePdfBytes);
```

---

### 7. Validação Segura de Webhooks (HMAC-SHA256)

```php
use Pairus\Webhooks;
use Pairus\Exceptions\InvalidSignatureException;

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_PAIRUS_SIGNATURE'] ?? '';

try {
    $event = Webhooks::constructEvent($payload, $signature, getenv('PAIRUS_WEBHOOK_SECRET'));
    
    if ($event->event === 'nfe.autorizada') {
        // Processar faturamento concluído
    }
} catch (InvalidSignatureException $e) {
    http_response_code(401);
    exit('Assinatura inválida');
}
```

---

## 📋 Tabela de Parâmetros: Obrigatório vs. Opcional

### Predição Fiscal (`$pairus->fiscal->predict`)

| Parâmetro | Tipo | Obrigatório? | Descrição & Regra Fiscal |
| :--- | :--- | :---: | :--- |
| `regimeTributario` | string | **Sim** | 'simples_nacional', 'lucro_presumido' ou 'lucro_real'. |
| `ufOrigem` | string | **Sim** | Sigla da UF do emitente (ex: 'SP'). |
| `ufDestino` | string | Não | Sigla da UF do destinatário. Se omitido, assume operação interna ($ufOrigem). |
| `finalidade` | string | Não | 'revenda' (padrão), 'consumo_final' ou 'industrializacao'. |
| `destinatarioContribuinte` | bool | Não | Define aplicação de DIFAL / ST (padrão: true). |
| `gtin` | string | Não* | Código de barras GTIN/EAN numérico (8, 12, 13 ou 14 dígitos). |
| `xProd` | string | Não* | Descrição comercial da mercadoria (para predição por IA/RAG). |
| `ncm` | string | Não | NCM atual de 8 dígitos para validação e auditoria. |
| `cest` | string | Não | Código CEST de 7 dígitos para validação de ICMS-ST. |
| `itens` | array | Não | Lista de itens para cálculo consolidado de NF-e completa em lote. |

*\*Nota: Pelo menos um identificador (`gtin`, `xProd` ou `ncm`) deve ser informado no modo individual.*

---

### NFS-e Nacional (`$pairus->nfse->emitir`)

| Parâmetro | Tipo | Obrigatório? | Descrição |
| :--- | :--- | :---: | :--- |
| `prestador.cnpj` | string | **Sim** | CNPJ do emissor prestador de serviços. |
| `tomador.cpf_cnpj` | string | **Sim** | CPF ou CNPJ do tomador do serviço. |
| `tomador.razao_social` | string | **Sim** | Razão social ou nome completo do tomador. |
| `servico.discriminacao` | string | **Sim** | Descrição clara dos serviços prestados. |
| `servico.valor_servicos` | float | **Sim** | Valor bruto total dos serviços (R$). |
| `servico.item_lista_servico` | string | Não | Item da LC 116/2003 (ex: '01.01'). |
| `servico.codigo_tributacao_municipio` | string | Não | Código municipal de tributação da prefeitura. |
| `servico.aliquota_iss` | float | Não | Alíquota nominal do ISS (ex: 2.0 para 2%). |
| `servico.iss_retido` | bool | Não | True se o ISS for retido na fonte pelo tomador. |

---

### Manifestação do Destinatário (`$pairus->dfe->manifestar`)

| Parâmetro | Tipo | Obrigatório? | Descrição & Regras Fiscais |
| :--- | :--- | :---: | :--- |
| `chaveAcesso` | string | **Sim** | Chave de 44 dígitos da NF-e emitida pelo fornecedor. |
| `cnpj` | string | **Sim** | CNPJ da sua empresa compradora/destinatária. |
| `tipoEvento` | string | **Sim** | '210210' (Ciência), '210200' (Confirmação), '210220' (Desconhecimento), '210240' (Não Realizada). |
| `justificativa` | string | Condicional | Obrigatória estritamente para '210240' (mínimo de 15 caracteres). |
| `ambiente` | string | Não | 'producao' (padrão) ou 'homologacao'. |

---

## 🛠️ Exemplos em Frameworks

### Laravel (Controller & Service)

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Pairus\PairusClient;

class FiscalController extends Controller
{
    public function consultar(string $gtin, PairusClient $pairus)
    {
        try {
            $produto = $pairus->products->get($gtin);
            return response()->json($produto);
        } catch (\Pairus\Exceptions\PairusApiException $e) {
            return response()->json(['erro' => $e->xMotivo], $e->statusCode);
        }
    }
}
```

### WordPress / WooCommerce

```php
add_action('woocommerce_process_product_meta', function ($postId) {
    $gtin = get_post_meta($postId, '_gtin', true);
    if (!empty($gtin)) {
        $pairus = new \Pairus\PairusClient(apiKey: defined('PAIRUS_API_KEY') ? PAIRUS_API_KEY : '');
        $produto = $pairus->products->get($gtin);
        
        update_post_meta($postId, '_ncm', $produto->ncm);
        update_post_meta($postId, '_cest', $produto->cest);
    }
});
```

---

## 🛡️ Tratamento de Erros e Padrão SEFAZ

Todas as exceções estendem `PairusApiException`, expondo os códigos de status e descrições do layout SEFAZ:

```php
try {
    $pairus->products->get('0000000000000');
} catch (\Pairus\Exceptions\AuthenticationException $e) {
    // Chave de API inválida ou expirada (HTTP 401)
    echo "Erro de autenticação: {$e->getMessage()}";
} catch (\Pairus\Exceptions\RateLimitException $e) {
    // Limite de requisições atingido (HTTP 429)
    echo "Aguarde {$e->retryAfter} segundos.";
} catch (\Pairus\Exceptions\PairusApiException $e) {
    // Erros de negócio ou rejeição fiscal
    echo "SEFAZ cStat: {$e->cStat} | Motivo: {$e->xMotivo}";
} catch (\Pairus\Exceptions\NetworkException $e) {
    // Falha de rede após retentativas exponenciais
    echo "Falha de conexão: {$e->getMessage()}";
}
```

---

## 📄 Licença

Distribuído sob a licença MIT. Consulte o arquivo [LICENSE](LICENSE) para mais detalhes.
