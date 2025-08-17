<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class ProximityAlert extends Notification implements ShouldQueue
{
    use Queueable;

    private array $proximityData;
    private string $alertType;

    public function __construct(array $proximityData, string $alertType = 'proximity_check')
    {
        $this->proximityData = $proximityData;
        $this->alertType = $alertType;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $subject = $this->proximityData['within_range'] 
            ? 'Delivery Within Range Alert' 
            : 'Delivery Out of Range Alert';

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Proximity Alert!')
            ->line($this->getAlertMessage())
            ->line('Distance: ' . $this->proximityData['distance'] . ' meters')
            ->line('Delivery ID: ' . ($this->proximityData['delivery_id'] ?? 'Unknown'))
            ->action('View Dashboard', url('/dashboard/map'));

        if (!$this->proximityData['within_range']) {
            $message->error();
        }

        return $message;
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => $this->alertType,
            'delivery_id' => $this->proximityData['delivery_id'] ?? null,
            'distance' => $this->proximityData['distance'],
            'within_range' => $this->proximityData['within_range'],
            'warehouse_coords' => $this->proximityData['warehouse_coords'] ?? null,
            'delivery_coords' => $this->proximityData['delivery_coords'] ?? null,
            'radius' => $this->proximityData['radius'] ?? 250,
            'message' => $this->getAlertMessage(),
            'timestamp' => now(),
        ];
    }

    private function getAlertMessage(): string
    {
        if ($this->proximityData['within_range']) {
            return "Delivery is within the alert radius ({$this->proximityData['distance']}m away)";
        }
        
        return "Delivery is outside the alert radius ({$this->proximityData['distance']}m away)";
    }
}
