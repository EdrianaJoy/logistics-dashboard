<?php

namespace App\Services;

use App\Notifications\ProximityAlert;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    private ProximityService $proximityService;

    public function __construct(ProximityService $proximityService)
    {
        $this->proximityService = $proximityService;
    }

    /**
     * Send proximity alert notification
     */
    public function sendProximityAlert(array $data, $recipient = null): bool
    {
        try {
            $proximityData = [
                'delivery_id' => $data['delivery_id'] ?? null,
                'distance' => $data['distance'],
                'within_range' => $data['within_range'],
                'warehouse_coords' => $data['warehouse_coords'] ?? null,
                'delivery_coords' => $data['delivery_coords'] ?? null,
                'radius' => $data['radius'] ?? 250,
            ];

            $notification = new ProximityAlert($proximityData);

            if ($recipient) {
                $recipient->notify($notification);
            } else {
                // Send to all users with notification permission
                $users = \App\Models\User::where('can_receive_alerts', true)->get();
                Notification::send($users, $notification);
            }

            // Log the alert
            Log::info('Proximity alert sent', $proximityData);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send proximity alert: ' . $e->getMessage(), $data);
            return false;
        }
    }

    /**
     * Send batch proximity alerts
     */
    public function sendBatchAlerts(array $deliveries, array $warehouseCoords, int $radius = 250): array
    {
        $results = [];

        foreach ($deliveries as $delivery) {
            try {
                $proximityResult = $this->proximityService->checkProximity(
                    $warehouseCoords,
                    $delivery['coords'],
                    $radius
                );

                $alertData = [
                    'delivery_id' => $delivery['id'],
                    'distance' => $proximityResult['distance'],
                    'within_range' => $proximityResult['within_range'],
                    'warehouse_coords' => $warehouseCoords,
                    'delivery_coords' => $delivery['coords'],
                    'radius' => $radius,
                ];

                // Only send alert if delivery is out of range or specifically requested
                if (!$proximityResult['within_range'] || ($delivery['force_alert'] ?? false)) {
                    $this->sendProximityAlert($alertData);
                    $results[$delivery['id']] = 'alert_sent';
                } else {
                    $results[$delivery['id']] = 'no_alert_needed';
                }

            } catch (\Exception $e) {
                Log::error("Failed to process delivery {$delivery['id']}: " . $e->getMessage());
                $results[$delivery['id']] = 'error';
            }
        }

        return $results;
    }

    /**
     * Get notification history for a user
     */
    public function getNotificationHistory($user, int $limit = 50): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $user->notifications()
            ->where('type', ProximityAlert::class)
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
    }

    /**
     * Mark notifications as read
     */
    public function markAsRead($user, array $notificationIds = []): bool
    {
        try {
            if (empty($notificationIds)) {
                $user->unreadNotifications->markAsRead();
            } else {
                $user->unreadNotifications()
                    ->whereIn('id', $notificationIds)
                    ->update(['read_at' => now()]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to mark notifications as read: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get unread notification count
     */
    public function getUnreadCount($user): int
    {
        return $user->unreadNotifications()
            ->where('type', ProximityAlert::class)
            ->count();
    }

    /**
     * Send real-time notification via broadcasting
     */
    public function sendRealTimeAlert(array $data): void
    {
        try {
            broadcast(new \App\Events\ProximityAlertEvent($data));
        } catch (\Exception $e) {
            Log::error('Failed to broadcast real-time alert: ' . $e->getMessage());
        }
    }
}
