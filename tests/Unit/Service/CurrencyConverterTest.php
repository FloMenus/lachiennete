<?php

namespace App\Tests\Unit\Service;

use App\Service\CurrencyConverter;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CurrencyConverterTest extends TestCase
{
    public function testConvertKeepsEurAsIs(): void
    {
        $httpClient = new MockHttpClient(function (): never {
            $this->fail('Aucun appel HTTP ne doit être fait pour une conversion en EUR.');
        });

        $result = $this->createConverter($httpClient)->convert(100.0, 'EUR');

        $this->assertSame(['amount' => 100.0, 'currency' => 'EUR'], $result);
    }

    public function testConvertFallsBackToEurForUnknownCurrency(): void
    {
        $httpClient = new MockHttpClient(function (): never {
            $this->fail('Aucun appel HTTP ne doit être fait pour une devise inconnue.');
        });

        $result = $this->createConverter($httpClient)->convert(50.0, 'XYZ');

        $this->assertSame(['amount' => 50.0, 'currency' => 'EUR'], $result);
    }

    public function testConvertUsesRateFromApi(): void
    {
        $httpClient = new MockHttpClient(new JsonMockResponse([
            'base' => 'EUR',
            'rates' => ['USD' => 1.10, 'JPY' => 160.0],
        ]));

        $result = $this->createConverter($httpClient)->convert(100.0, 'USD');

        $this->assertSame('USD', $result['currency']);
        $this->assertEqualsWithDelta(110.0, $result['amount'], 0.001);
    }

    public function testConvertFallsBackToEurWhenApiFails(): void
    {
        $httpClient = new MockHttpClient(new MockResponse('Server error', ['http_code' => 500]));

        $result = $this->createConverter($httpClient)->convert(80.0, 'USD');

        $this->assertSame(['amount' => 80.0, 'currency' => 'EUR'], $result);
    }

    public function testConvertCachesRatesBetweenCalls(): void
    {
        $callCount = 0;
        $httpClient = new MockHttpClient(function () use (&$callCount): JsonMockResponse {
            ++$callCount;

            return new JsonMockResponse(['rates' => ['USD' => 2.0]]);
        });

        $converter = $this->createConverter($httpClient);
        $converter->convert(10.0, 'USD');
        $converter->convert(20.0, 'USD');

        $this->assertSame(1, $callCount, 'Les taux doivent être mis en cache après le premier appel.');
    }

    public function testChoicesMapsLabelsToIsoCodes(): void
    {
        $choices = CurrencyConverter::choices();

        $this->assertSame('EUR', $choices['Euro (€)']);
        $this->assertSame('USD', $choices['Dollar américain ($)']);
        $this->assertCount(\count(CurrencyConverter::CURRENCIES), $choices);
    }

    private function createConverter(HttpClientInterface $httpClient): CurrencyConverter
    {
        return new CurrencyConverter($httpClient, new ArrayAdapter(), new NullLogger());
    }
}
