<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CurrencyConverter
{
    private const API_URL = 'https://api.frankfurter.dev/v1/latest';
    private const CACHE_KEY = 'exchange_rates_eur';
    private const CACHE_TTL = 3600;

    public const CURRENCIES = [
        'EUR' => ['label' => 'Euro (€)', 'symbol' => '€', 'decimals' => 2],
        'USD' => ['label' => 'Dollar américain ($)', 'symbol' => '$', 'decimals' => 2],
        'GBP' => ['label' => 'Livre sterling (£)', 'symbol' => '£', 'decimals' => 2],
        'CHF' => ['label' => 'Franc suisse (CHF)', 'symbol' => 'CHF', 'decimals' => 2],
        'CAD' => ['label' => 'Dollar canadien ($ CA)', 'symbol' => '$ CA', 'decimals' => 2],
        'JPY' => ['label' => 'Yen japonais (¥)', 'symbol' => '¥', 'decimals' => 0],
    ];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Choix pour un ChoiceType Symfony : libellé => code ISO.
     *
     * @return array<string, string>
     */
    public static function choices(): array
    {
        $choices = [];
        foreach (self::CURRENCIES as $code => $config) {
            $choices[$config['label']] = $code;
        }

        return $choices;
    }

    /**
     * Convertit un montant en EUR vers la devise demandée.
     * Retombe sur l'EUR si la devise est inconnue ou si les taux sont indisponibles.
     *
     * @return array{amount: float, currency: string}
     */
    public function convert(float $amountInEur, string $currency): array
    {
        if ('EUR' === $currency || !isset(self::CURRENCIES[$currency])) {
            return ['amount' => $amountInEur, 'currency' => 'EUR'];
        }

        $rate = $this->getRates()[$currency] ?? null;
        if (null === $rate) {
            return ['amount' => $amountInEur, 'currency' => 'EUR'];
        }

        return ['amount' => $amountInEur * $rate, 'currency' => $currency];
    }

    /**
     * Taux EUR → devises supportées, mis en cache 1 h.
     *
     * @return array<string, float>
     */
    private function getRates(): array
    {
        try {
            return $this->cache->get(self::CACHE_KEY, function (ItemInterface $item): array {
                $item->expiresAfter(self::CACHE_TTL);

                $symbols = array_diff(array_keys(self::CURRENCIES), ['EUR']);
                $response = $this->httpClient->request('GET', self::API_URL, [
                    'query' => ['base' => 'EUR', 'symbols' => implode(',', $symbols)],
                    'timeout' => 5,
                ]);

                return $response->toArray()['rates'] ?? [];
            });
        } catch (\Throwable $e) {
            $this->logger->warning('Taux de change indisponibles, affichage en EUR : {message}', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
