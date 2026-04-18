<?php

namespace App\Service;

use App\Entity\Offre;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Fetches current weather for an offer's destination via OpenWeatherMap.
 * Results are cached in offre.weatherCache (JSON) for 3 hours.
 *
 * ENV variable required:
 *   OPENWEATHER_API_KEY=your_key_here
 *
 * Free-tier endpoint used: api.openweathermap.org/data/2.5/weather
 */
class WeatherService
{
    public function __construct(
        private readonly HttpClientInterface     $httpClient,
        private readonly EntityManagerInterface  $em,
        private readonly string                  $apiKey,   // injected via services.yaml
    ) {}

    /**
     * Returns weather array (or null on failure).
     * Uses 3-hour cache stored inside the Offre entity itself.
     */
    public function getWeatherForOffre(Offre $offre): ?array
    {
        if (!$offre->getDestination()) {
            return null;
        }

        // ── Check cache ──────────────────────────────────
        if ($offre->getWeatherCache()) {
            $cached = json_decode($offre->getWeatherCache(), true);
            if (isset($cached['_cached_at']) && (time() - $cached['_cached_at']) < 10800) {
                return $cached;
            }
        }

        // ── Fetch from API ───────────────────────────────
        try {
            $response = $this->httpClient->request('GET', 'https://api.openweathermap.org/data/2.5/weather', [
                'query' => [
                    'q'     => $offre->getDestination(),
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang'  => 'fr',
                ],
                'timeout' => 5,
            ]);

            $data = $response->toArray();

            $weather = [
                '_cached_at'  => time(),
                'city'        => $data['name'] ?? $offre->getDestination(),
                'country'     => $data['sys']['country'] ?? '',
                'temp'        => round($data['main']['temp'] ?? 0),
                'feels_like'  => round($data['main']['feels_like'] ?? 0),
                'humidity'    => $data['main']['humidity'] ?? 0,
                'description' => ucfirst($data['weather'][0]['description'] ?? ''),
                'icon'        => $data['weather'][0]['icon'] ?? '01d',
                'wind_speed'  => round(($data['wind']['speed'] ?? 0) * 3.6, 1), // m/s → km/h
            ];

            // Persist cache back to entity
            $offre->setWeatherCache(json_encode($weather));
            $this->em->flush();

            return $weather;

        } catch (\Throwable $e) {
            // Silent fail – weather is non-critical
            return null;
        }
    }

    /**
     * Returns an emoji for a weather icon code.
     */
    public static function iconToEmoji(string $icon): string
    {
        return match(substr($icon, 0, 2)) {
            '01' => '☀️',
            '02' => '🌤️',
            '03' => '⛅',
            '04' => '☁️',
            '09' => '🌧️',
            '10' => '🌦️',
            '11' => '⛈️',
            '13' => '❄️',
            '50' => '🌫️',
            default => '🌡️',
        };
    }
}