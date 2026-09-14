# PAIRUS Product Data SDK para PHP 🐘

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pairus/product-data.svg?style=flat-square)](https://packagist.org/packages/pairus/product-data)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.1-777BB4.svg?style=flat-square)](https://www.php.net/)

SDK oficial da **PAIRUS Soluções Tecnológicas** em PHP moderno (PHP 8.1+) para integração com o catálogo global de produtos GTIN/EAN, motor de predição fiscal da **Reforma Tributária (IBS, CBS e Imposto Seletivo)** e validação segura de Webhooks.

---

## 📦 Instalação

Instale via [Composer](https://getcomposer.org/):

```bash
composer require pairus/product-data
```

---

## 🚀 Inicialização Rápida

```php
<?php

require_once 'vendor/autoload.php';

use Pairus\PairusClient;

// Inicialização com chave explícita ou via variável de ambiente PAIRUS_API_KEY
$client = new PairusClient(
    apiKey: 'sua_chave_de_api_pairus'
);
```

---

## 🔍 1. Consulta de Produtos por GTIN/EAN

Consulte informações detalhadas de produtos catalogados:

```php
try {
    $produto = $client->products->get('7891000100103');

    echo "Descrição: " . $produto->xProd . PHP_EOL;
    echo "NCM: " . $produto->ncm . PHP_EOL;
    echo "CEST: " . $produto->cest . PHP_EOL;
    echo "Marca: " . $produto->marca . PHP_EOL;
    echo "Peso Bruto: " . $produto->pesoBruto . " kg" . PHP_EOL;
} catch (\Pairus\Exceptions\PairusApiException $e) {
    echo "Erro na consulta [{$e->cStat}]: {$e->xMotivo}";
}
```

---

## ⚖️ 2. Motor de Predição Fiscal e Reforma Tributária (IBS / CBS / IS)

Obtenha parâmetros fiscais completos e em total conformidade com a Reforma Tributária para emissão de NF-e:

```php
$resultado = $client->fiscal->predict(
    regimeTributario: 'simples_nacional', // 'simples_nacional', 'lucro_presumido' ou 'lucro_real'
    ufOrigem: 'SP',
    ufDestino: 'RJ',
    finalidade: 'revenda',               // 'revenda', 'consumo_final' ou 'industrializacao'
    destinatarioContribuinte: true,
    gtin: '7894900010015',
    xProd: 'REFRIGERANTE COCA COLA 2L'
);

$fiscal = $resultado->dadosTributarios;

echo "NCM Sugerido: " . $fiscal->ncmSugerido . PHP_EOL;
echo "CFOP: " . $fiscal->cfop . " (Devolução: " . $fiscal->cfopDevolucao . ")" . PHP_EOL;
echo "ICMS CST/CSOSN: " . $fiscal->icmsCstCsosn . PHP_EOL;

// Reforma Tributária (IBS e CBS)
echo "IBS/CBS CST: " . $fiscal->ibscbs->cst . PHP_EOL;               // Ex: "000"
echo "Classificação Tributária: " . $fiscal->ibscbs->cClassTrib . PHP_EOL; // Ex: "000001"
echo "Alíquota Efetiva CBS: " . $fiscal->ibscbs->cbsAliquotaEfetiva . "%" . PHP_EOL;
echo "Alíquota Efetiva IBS: " . $fiscal->ibscbs->ibsAliquotaEfetiva . "%" . PHP_EOL;

// Imposto Seletivo (se aplicável)
if ($fiscal->impostoSeletivo !== null) {
    echo "Imposto Seletivo (IS): " . $fiscal->impostoSeletivo->aliquota . "%" . PHP_EOL;
}
```

---

## 🪝 3. Validação Segura de Webhooks (HMAC-SHA256)

Proteja seus endpoints de webhook contra ataques de repetição e *timing attacks* (compatível com Laravel, Symfony, WordPress/WooCommerce e PHP puro):

```php
use Pairus\Webhooks;
use Pairus\Exceptions\InvalidSignatureException;

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_PAIRUS_SIGNATURE'] ?? null;
$webhookSecret = getenv('PAIRUS_WEBHOOK_SECRET');

try {
    $evento = Webhooks::constructEvent($payload, $signature, $webhookSecret);

    echo "Evento recebido: " . $evento->event;
    $dados = $evento->data;
} catch (InvalidSignatureException $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Assinatura HMAC inválida.']);
    exit;
}
```

---

## 🧾 4. Emissão de NF-e / NFC-e com Autocura SEFAZ

Emita notas fiscais com saneamento tributário inteligente em tempo real e homologação direta na SEFAZ:

```php
// Emissão de NF-e (Modelo 55) com correção automática de inconsistências
$nfe = $client->emissao->emitirNfe([
    'natureza_operacao' => 'VENDA DE MERCADORIAS',
    'autocura' => true,
    'itens' => [
        [
            'cProd' => 'PROD-001',
            'xProd' => 'Refrigerante Coca-Cola 350ml',
            'NCM' => '22021000',
            'CFOP' => '5102',
            'uCom' => 'UN',
            'qCom' => 10.0,
            'vUnCom' => 5.0,
            'vProd' => 50.0,
        ],
    ],
]);

echo "Status SEFAZ: [{$nfe->cStat}] {$nfe->xMotivo}" . PHP_EOL;
echo "Chave de Acesso: {$nfe->chaveAcesso}" . PHP_EOL;
echo "DANFE PDF: {$nfe->danfeUrl}" . PHP_EOL;

// Simulação prévia com CUSTO ZERO de créditos
$simulacao = $client->emissao->simular([
    'natureza_operacao' => 'TESTE PREVIO',
    'itens' => [['cProd' => 'P1', 'xProd' => 'Item Teste', 'NCM' => '22021000', 'CFOP' => '5102', 'uCom' => 'UN', 'qCom' => 1.0, 'vUnCom' => 10.0, 'vProd' => 10.0]],
]);
echo "Espelho do DANFE: {$simulacao->danfeUrl}" . PHP_EOL;

// Cancelamento homologado perante o Fisco
$cancelamento = $client->emissao->cancelar(
    chaveAcesso: $nfe->chaveAcesso,
    justificativa: 'Desistência de compra pelo cliente dentro do prazo legal'
);
echo "Protocolo de cancelamento: {$cancelamento->protocolo}" . PHP_EOL;
```

---

## 🛠️ Exemplos em Frameworks

### Laravel Controller
```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Pairus\PairusClient;

class ProductController extends Controller
{
    public function show(string $gtin, PairusClient $pairus)
    {
        $produto = $pairus->products->get($gtin);
        return response()->json($produto);
    }
}
```

### WooCommerce / WordPress Hook
```php
add_action('woocommerce_process_product_meta', function ($post_id) {
    $gtin = get_post_meta($post_id, '_gtin', true);
    if (!empty($gtin)) {
        $client = new \Pairus\PairusClient(apiKey: defined('PAIRUS_API_KEY') ? PAIRUS_API_KEY : '');
        $produto = $client->products->get($gtin);
        
        update_post_meta($post_id, '_ncm', $produto->ncm);
        update_post_meta($post_id, '_cest', $produto->cest);
    }
});
```

---

## 📄 Licença

Distribuído sob a licença MIT. Consulte o arquivo [LICENSE](LICENSE) para obter mais informações.
