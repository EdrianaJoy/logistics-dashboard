<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\ProximityService;
use App\Services\NotificationService;
use App\Models\ProximityAlert;
use App\Events\ProximityAlertEvent;

class ProximityController extends Controller
{
    private ProximityService $proximityService;
    private NotificationService $notificationService;

    public function __construct(ProximityService $proximityService, NotificationService $notificationService)
    {
        $this->proximityService = $proximityService;
        $this->notificationService = $notificationService;
    }

    /**
     * Check proximity using the Flask API with enhanced logging and notifications
     */
    public function checkProximity(Request $request)
    {
        // Validate the request
        $request->validate([
            'warehouse' => 'required|array|size:2',
            'delivery' => 'required|array|size:2',
            'radius' => 'nullable|numeric|min:1',
            'delivery_id' => 'nullable|string',
            'send_notification' => 'nullable|boolean'
        ]);

        // Prepare data for the Flask API
        $data = [
            'warehouse' => $request->warehouse,
            'delivery' => $request->delivery,
            'radius' => $request->radius ?? 250
        ];

        try {
            // Make HTTP request to your Flask proximity service
            $response = Http::timeout(30)
                ->post('http://localhost:8080/check_proximity', $data);

            // Check if the request was successful
            if ($response->successful()) {
                $proximityData = $response->json();
                
                // Log the alert to database
                $alertRecord = ProximityAlert::create([
                    'delivery_id' => $request->delivery_id,
                    'warehouse_lat' => $request->warehouse[0],
                    'warehouse_lng' => $request->warehouse[1],
                    'delivery_lat' => $request->delivery[0],
                    'delivery_lng' => $request->delivery[1],
                    'distance' => $proximityData['distance'],
                    'radius' => $data['radius'],
                    'within_range' => $proximityData['within_range'],
                    'alert_type' => 'proximity_check',
                    'status' => 'sent',
                    'alert_sent_at' => now(),
                    'metadata' => [
                        'user_agent' => $request->userAgent(),
                        'ip_address' => $request->ip(),
                    ]
                ]);

                // Send notification if requested or if out of range
                if ($request->send_notification || !$proximityData['within_range']) {
                    $notificationData = [
                        'delivery_id' => $request->delivery_id,
                        'distance' => $proximityData['distance'],
                        'within_range' => $proximityData['within_range'],
                        'warehouse_coords' => $request->warehouse,
                        'delivery_coords' => $request->delivery,
                        'radius' => $data['radius'],
                    ];
                    
                    $this->notificationService->sendProximityAlert($notificationData);
                }

                // Broadcast real-time update
                broadcast(new ProximityAlertEvent([
                    'delivery_id' => $request->delivery_id,
                    'lat' => $request->delivery[0],
                    'lng' => $request->delivery[1],
                    'distance' => $proximityData['distance'],
                    'within_range' => $proximityData['within_range'],
                    'alert_id' => $alertRecord->id
                ], 'proximity_update'));

                return response()->json([
                    'success' => true,
                    'data' => $proximityData,
                    'alert_id' => $alertRecord->id
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to check proximity',
                'error' => $response->body()
            ], $response->status());

        } catch (\Exception $e) {
            // Log failed attempt
            ProximityAlert::create([
                'delivery_id' => $request->delivery_id,
                'warehouse_lat' => $request->warehouse[0],
                'warehouse_lng' => $request->warehouse[1],
                'delivery_lat' => $request->delivery[0],
                'delivery_lng' => $request->delivery[1],
                'distance' => 0,
                'radius' => $data['radius'],
                'within_range' => false,
                'alert_type' => 'proximity_check',
                'status' => 'failed',
                'metadata' => [
                    'error' => $e->getMessage(),
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                ]
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error connecting to proximity service',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Example of making multiple HTTP requests
     */
    public function batchProximityCheck(Request $request)
    {
        $deliveries = $request->validate([
            'warehouse' => 'required|array|size:2',
            'deliveries' => 'required|array',
            'deliveries.*.coords' => 'required|array|size:2',
            'deliveries.*.id' => 'required',
            'radius' => 'nullable|numeric|min:1'
        ]);

        $results = [];
        $warehouse = $deliveries['warehouse'];
        $radius = $deliveries['radius'] ?? 250;

        // Use Http::pool for concurrent requests
        $responses = Http::pool(fn ($pool) => 
            collect($deliveries['deliveries'])->mapWithKeys(fn ($delivery) => [
                $delivery['id'] => $pool->post('http://localhost:8080/check_proximity', [
                    'warehouse' => $warehouse,
                    'delivery' => $delivery['coords'],
                    'radius' => $radius
                ])
            ])
        );

        foreach ($responses as $deliveryId => $response) {
            if ($response->successful()) {
                $results[$deliveryId] = $response->json();
            } else {
                $results[$deliveryId] = [
                    'error' => 'Failed to check proximity for delivery ' . $deliveryId
                ];
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results
        ]);
    }

    /**
     * Show interactive map dashboard
     */
    public function showMapDashboard()
    {
        return view('dashboard.map');
    }

    /**
     * Show heatmap dashboard
     */
    public function showHeatmapDashboard()
    {
        return view('dashboard.heatmap');
    }

    /**
     * Show real-time tracking dashboard
     */
    public function showRealTimeDashboard()
    {
        return view('dashboard.realtime');
    }

    /**
     * Get alert statistics
     */
    public function getAlertStats(Request $request)
    {
        $days = $request->get('days', 7);
        $stats = ProximityAlert::getAlertStats($days);
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Get delivery statistics
     */
    public function getDeliveryStats(Request $request, string $deliveryId)
    {
        $stats = ProximityAlert::getDeliveryStats($deliveryId);
        
        return response()->json([
            'success' => true,
            'delivery_id' => $deliveryId,
            'stats' => $stats
        ]);
    }

    /**
     * Get notification history
     */
    public function getNotificationHistory(Request $request)
    {
        $user = $request->user();
        $notifications = $this->notificationService->getNotificationHistory($user);
        
        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark notifications as read
     */
    public function markNotificationsAsRead(Request $request)
    {
        $user = $request->user();
        $notificationIds = $request->get('notification_ids', []);
        
        $success = $this->notificationService->markAsRead($user, $notificationIds);
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Notifications marked as read' : 'Failed to mark notifications as read'
        ]);
    }
}
