<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class ProximityControllerTest extends TestCase
{
    public function test_proximity_check_successful()
    {
        // Mock the HTTP client
        Http::fake([
            'localhost:8080/check_proximity' => Http::response([
                'distance' => 150.5,
                'within_range' => true
            ], 200)
        ]);

        $response = $this->postJson('/api/proximity/check', [
            'warehouse' => [40.7128, -74.0060],
            'delivery' => [40.7614, -73.9776],
            'radius' => 250
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'distance' => 150.5,
                        'within_range' => true
                    ]
                ]);
    }

    public function test_proximity_check_service_unavailable()
    {
        // Mock a failed response
        Http::fake([
            'localhost:8080/check_proximity' => Http::response('Service unavailable', 503)
        ]);

        $response = $this->postJson('/api/proximity/check', [
            'warehouse' => [40.7128, -74.0060],
            'delivery' => [40.7614, -73.9776],
            'radius' => 250
        ]);

        $response->assertStatus(503)
                ->assertJson([
                    'success' => false
                ]);
    }

    public function test_batch_proximity_check()
    {
        Http::fake([
            'localhost:8080/check_proximity' => Http::response([
                'distance' => 150.5,
                'within_range' => true
            ], 200)
        ]);

        $response = $this->postJson('/api/proximity/batch', [
            'warehouse' => [40.7128, -74.0060],
            'deliveries' => [
                ['id' => 1, 'coords' => [40.7614, -73.9776]],
                ['id' => 2, 'coords' => [40.7589, -73.9851]]
            ],
            'radius' => 250
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ]);
    }
}
