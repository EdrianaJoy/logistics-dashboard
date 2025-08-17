<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ProximityService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.proximity.url', 'http://localhost:8080');
    }

    /**
     * Basic HTTP request
     */
    public function checkProximity(array $warehouse, array $delivery, int $radius = 250): array
    {
        $response = Http::post("{$this->baseUrl}/check_proximity", [
            'warehouse' => $warehouse,
            'delivery' => $delivery,
            'radius' => $radius
        ]);

        return $response->json();
    }

    /**
     * With headers and authentication
     */
    public function authenticatedRequest(array $data): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.proximity.token'),
            'Content-Type' => 'application/json',
            'X-API-Key' => config('services.proximity.api_key')
        ])->timeout(30)->post("{$this->baseUrl}/check_proximity", $data);

        return $response->json();
    }

    /**
     * With retry logic
     */
    public function resilientRequest(array $data): array
    {
        $response = Http::retry(3, 100) // 3 retries with 100ms delay
            ->timeout(30)
            ->post("{$this->baseUrl}/check_proximity", $data);

        return $response->json();
    }

    /**
     * Async/Concurrent requests
     */
    public function batchCheck(array $warehouse, array $deliveries, int $radius = 250): array
    {
        $responses = Http::pool(fn ($pool) => 
            collect($deliveries)->mapWithKeys(fn ($delivery, $index) => [
                $index => $pool->post("{$this->baseUrl}/check_proximity", [
                    'warehouse' => $warehouse,
                    'delivery' => $delivery,
                    'radius' => $radius
                ])
            ])
        );

        return collect($responses)
            ->map(fn ($response) => $response->successful() ? $response->json() : null)
            ->filter()
            ->toArray();
    }

    /**
     * With file uploads (if needed)
     */
    public function uploadFile(string $filePath): array
    {
        $response = Http::attach('file', file_get_contents($filePath), 'coordinates.csv')
            ->post("{$this->baseUrl}/upload");

        return $response->json();
    }

    /**
     * Stream large responses
     */
    public function streamResponse(): \Generator
    {
        $response = Http::withOptions(['stream' => true])
            ->get("{$this->baseUrl}/stream");

        foreach ($response->collect() as $item) {
            yield $item;
        }
    }

    /**
     * Error handling
     */
    public function safeRequest(array $data): array
    {
        try {
            $response = Http::timeout(30)
                ->post("{$this->baseUrl}/check_proximity", $data);

            if ($response->successful()) {
                return $response->json();
            }

            // Handle different HTTP status codes
            match ($response->status()) {
                404 => throw new \Exception('Proximity service not found'),
                422 => throw new \Exception('Invalid data: ' . $response->json('message')),
                500 => throw new \Exception('Proximity service error'),
                default => throw new \Exception('Unknown error: ' . $response->status())
            };

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new \Exception('Cannot connect to proximity service');
        } catch (\Illuminate\Http\Client\RequestException $e) {
            throw new \Exception('Request failed: ' . $e->getMessage());
        }
    }
}
