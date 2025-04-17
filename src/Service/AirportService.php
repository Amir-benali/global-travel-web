<?php

namespace App\Service;

use JsonException;
use RuntimeException;

class AirportService
{
    private const API_URL = 'https://airportgap.com/api/airports';

    public function fetchAirportNames(): array
    {
        $response = $this->makeApiRequest();
        $airportNames = [];

        if (isset($response['data']) && is_array($response['data'])) {
            foreach ($response['data'] as $airport) {
                if (isset($airport['attributes']['name'])) {

                    $airportNames[] = $airport['attributes']['name'];
                }
            }
        }

        // Debugging
        error_log('Fetched Airport Names: ' . implode(', ', $airportNames));

        return $airportNames;
    }

    public function fetchAirportCountries(): array
    {
        $response = $this->makeApiRequest();
        $airportCountries = [];

        if (isset($response['data']) && is_array($response['data'])) {
            foreach ($response['data'] as $airport) {
                if (isset($airport['attributes']['country'])) {
                    $airportCountries[] = $airport['attributes']['country'];
                }
            }
        }

        // Supprime les doublons tout en conservant l'ordre
        $airportCountries = array_values(array_unique($airportCountries));

        // Debugging
        error_log('Fetched Airport Countries: ' . implode(', ', $airportCountries));

        return $airportCountries;
    }

    protected function makeApiRequest(): array
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Désactiver la vérification SSL

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new RuntimeException('Erreur lors de la requête API : ' . curl_error($ch));
        }

        curl_close($ch);

        try {
            return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Erreur lors du décodage JSON : ' . $e->getMessage());
        }
    }
}