<?php

declare(strict_types=1);

namespace Pairus\Resources;

use Pairus\Models\EmissaoResponse;
use Pairus\Models\EventoResponse;

/**
 * Namespace de recursos para Emissão de NF-e, NFC-e e Governança de Eventos SEFAZ.
 */
class EmissaoResource extends BaseResource
{
    /**
     * Emite uma Nota Fiscal Eletrônica (NF-e Modelo 55) perante a SEFAZ.
     *
     * @param array $payload Dados da nota fiscal
     * @param string|null $idempotencyKey Identificador único da venda; se nulo, um UUID é gerado e reutilizado
     *                                    nas retentativas automáticas (reenvio seguro, sem nota duplicada)
     * @return EmissaoResponse Retorno oficial com status, chave de acesso, XML e DANFE
     */
    public function emitirNfe(array $payload, ?string $idempotencyKey = null): EmissaoResponse
    {
        $data = $this->request(
            'POST', '/v1/nfe/emitir', jsonData: $payload, extraHeaders: self::cabecalhoIdempotencia($idempotencyKey)
        );
        return EmissaoResponse::fromArray($data);
    }

    /**
     * Emite uma Nota Fiscal de Consumidor Eletrônica (NFC-e Modelo 65) perante a SEFAZ.
     *
     * @param array $payload Dados da nota fiscal
     * @param string|null $idempotencyKey Identificador único da venda no PDV; se nulo, um UUID é gerado e
     *                                    reutilizado nas retentativas automáticas (reenvio seguro)
     * @return EmissaoResponse Retorno oficial da NFC-e com QRCode
     */
    public function emitirNfce(array $payload, ?string $idempotencyKey = null): EmissaoResponse
    {
        $data = $this->request(
            'POST', '/v1/nfce/emitir', jsonData: $payload, extraHeaders: self::cabecalhoIdempotencia($idempotencyKey)
        );
        return EmissaoResponse::fromArray($data);
    }

    /**
     * Simula uma emissão fiscal com custo ZERO de créditos para testes prévios.
     *
     * @param array $payload Dados da nota fiscal para simulação
     * @return EmissaoResponse Retorno com cálculos tributários e espelho de DANFE
     */
    public function simular(array $payload): EmissaoResponse
    {
        $data = $this->request('POST', '/v1/nfe/simular', jsonData: $payload);
        return EmissaoResponse::fromArray($data);
    }

    /**
     * Homologa o cancelamento oficial de uma nota fiscal perante a SEFAZ.
     *
     * @param string $chaveAcesso Chave de acesso de 44 dígitos da nota
     * @param string $justificativa Motivo do cancelamento (mínimo 15 caracteres)
     * @param string|null $protocoloAutorizacao Protocolo da autorização original
     * @return EventoResponse
     */
    public function cancelar(
        string $chaveAcesso,
        string $justificativa,
        ?string $protocoloAutorizacao = null
    ): EventoResponse {
        $payload = [
            'chave_acesso' => $chaveAcesso,
            'justificativa' => $justificativa,
        ];
        if ($protocoloAutorizacao !== null) {
            $payload['protocolo_autorizacao'] = $protocoloAutorizacao;
        }

        $data = $this->request('POST', '/v1/nfe/cancelar', jsonData: $payload);
        return EventoResponse::fromArray($data);
    }

    /**
     * Emite uma Carta de Correção Eletrônica (CC-e) perante a SEFAZ.
     *
     * @param string $chaveAcesso Chave de acesso de 44 dígitos da nota
     * @param string $correcao Texto retificador (mínimo 15 caracteres)
     * @param int $sequenciaEvento Sequencial do evento (padrão 1)
     * @return EventoResponse
     */
    public function cartaCorrecao(
        string $chaveAcesso,
        string $correcao,
        int $sequenciaEvento = 1
    ): EventoResponse {
        $payload = [
            'chave_acesso' => $chaveAcesso,
            'correcao' => $correcao,
            'sequencia_evento' => $sequenciaEvento,
        ];

        $data = $this->request('POST', '/v1/nfe/carta-correcao', jsonData: $payload);
        return EventoResponse::fromArray($data);
    }

    /**
     * Inutiliza uma faixa de numeração quebrada perante a SEFAZ (Ajuste SINIEF 07/05).
     *
     * @param string $cnpjEmitente CNPJ da empresa emitente (14 dígitos)
     * @param int $serie Série da nota fiscal
     * @param int $numeroInicial Número inicial da faixa
     * @param int $numeroFinal Número final da faixa
     * @param string $justificativa Motivo da quebra de sequência
     * @param int|null $ano Ano com 2 dígitos (ex: 26)
     * @param int $modelo Modelo 55 (NF-e) ou 65 (NFC-e)
     * @return EventoResponse
     */
    public function inutilizar(
        string $cnpjEmitente,
        int $serie,
        int $numeroInicial,
        int $numeroFinal,
        string $justificativa,
        ?int $ano = null,
        int $modelo = 55
    ): EventoResponse {
        $payload = [
            'cnpj_emitente' => $cnpjEmitente,
            'serie' => $serie,
            'numero_inicial' => $numeroInicial,
            'numero_final' => $numeroFinal,
            'justificativa' => $justificativa,
            'modelo' => (string) $modelo,
        ];
        if ($ano !== null) {
            $payload['ano'] = $ano;
        }

        $data = $this->request('POST', '/v1/nfe/inutilizar', jsonData: $payload);
        return EventoResponse::fromArray($data);
    }

    /**
     * Cabeçalho Idempotency-Key da emissão: a chave informada ou um UUID v4 gerado por chamada.
     *
     * @return array<string, string>
     */
    private static function cabecalhoIdempotencia(?string $idempotencyKey): array
    {
        if ($idempotencyKey === null || $idempotencyKey === '') {
            $bytes = random_bytes(16);
            $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
            $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
            $idempotencyKey = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
        }
        return ['Idempotency-Key' => $idempotencyKey];
    }
}
