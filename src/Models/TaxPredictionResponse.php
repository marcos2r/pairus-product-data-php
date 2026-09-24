<?php

declare(strict_types=1);

namespace Pairus\Models;

/**
 * Resposta oficial da rota de predição fiscal /v2/fiscal/predict.
 */
class TaxPredictionResponse
{
    /**
     * @param TaxPredictionItemResult[]|null $resultados
     */
    public function __construct(
        public readonly string $status,
        public readonly string $provider,
        public readonly ?string $xProd = null,
        public readonly ?TaxPredictionData $dadosTributarios = null,
        public readonly ?array $resultados = null,
        public readonly array $raw = [],
        public readonly ?string $timestamp = null,
        public readonly ?string $motorIa = null
    ) {}

    public static function fromArray(array $data): self
    {
        $dadosTributarios = isset($data['dados_tributarios']) && is_array($data['dados_tributarios'])
            ? TaxPredictionData::fromArray($data['dados_tributarios'])
            : null;

        $resultados = null;
        if (isset($data['resultados']) && is_array($data['resultados'])) {
            $resultados = [];
            foreach ($data['resultados'] as $item) {
                if (is_array($item)) {
                    $resultados[] = TaxPredictionItemResult::fromArray($item);
                }
            }
        }

        return new self(
            status: (string) ($data['status'] ?? 'sucesso'),
            provider: (string) ($data['provider'] ?? 'pairus_fiscal_engine'),
            xProd: isset($data['xProd']) ? (string) $data['xProd'] : null,
            dadosTributarios: $dadosTributarios,
            resultados: $resultados,
            raw: $data,
            timestamp: isset($data['timestamp']) ? (string) $data['timestamp'] : null,
            motorIa: isset($data['motor_ia']) ? (string) $data['motor_ia'] : null
        );
    }
}
