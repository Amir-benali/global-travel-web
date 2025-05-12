<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Cache\CacheItem;

class CityDataProvider
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private CacheInterface $cache,
        private string $userAgent
    ) {}

    public function getCountries(): array
    {
        return $this->cache->get('countries_data', function (CacheItem $item) {
            $item->expiresAfter(86400); // 24 hours cache

            try {
                $response = $this->httpClient->request('GET', 'https://countriesnow.space/api/v0.1/countries', [
                    'headers' => ['User-Agent' => $this->userAgent],
                ]);

                $data = $response->toArray();

                $countries = [];
                foreach ($data['data'] as $country) {
                    if (isset($country['iso2'], $country['country'])) {
                        $countries[$country['iso2']] = $country['country'];
                    }
                }

                asort($countries);
                return $countries;

            } catch (\Exception $e) {
                $this->logger->error('Countries API failed: ' . $e->getMessage());
                return $this->getFallbackCountries();
            }
        });
    }

    public function getCitiesForCountry(string $countryCode): array
    {
        $cacheKey = 'cities_' . strtoupper($countryCode);

        return $this->cache->get($cacheKey, function (CacheItem $item) use ($countryCode) {
            $item->expiresAfter(3600); // 1 hour cache

            $countries = $this->getCountries();
            $countryName = $countries[strtoupper($countryCode)] ?? null;

            if (!$countryName) {
                $this->logger->warning("Country code not found: $countryCode");
                return $this->getFallbackCities($countryCode);
            }

            try {
                sleep(1); // Rate limiting

                $response = $this->httpClient->request('POST', 'https://countriesnow.space/api/v0.1/countries/cities', [
                    'headers' => ['User-Agent' => $this->userAgent],
                    'json' => ['country' => $countryName],
                ]);

                $data = $response->toArray();
                return $data['data'] ?? [];

            } catch (\Exception $e) {
                $this->logger->error("Cities API failed for $countryCode: " . $e->getMessage());
                return $this->getFallbackCities($countryCode);
            }
        });
    }

    private function getFallbackCountries(): array
    {
        return [
            'US' => 'United States',
            'FR' => 'France',
            'TN' => 'Tunisia',
            'AW' => 'Aruba',
            // Add more fallback countries as needed
        ];
    }

    private function getFallbackCities(string $countryCode): array
    {
        return [
            'US' => ['New York', 'Los Angeles', 'Chicago'],
            'FR' => ['Paris', 'Marseille', 'Lyon'],
            'TN' => ['Tunis', 'Sfax', 'Sousse'],
            'AW' => ['Oranjestad', 'Noord', 'Santa Cruz'],
        ][strtoupper($countryCode)] ?? [];
    }
}