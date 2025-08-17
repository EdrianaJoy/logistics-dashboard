<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ProximityAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'warehouse_lat',
        'warehouse_lng',
        'delivery_lat',
        'delivery_lng',
        'distance',
        'radius',
        'within_range',
        'alert_type',
        'status',
        'metadata',
        'alert_sent_at',
    ];

    protected $casts = [
        'within_range' => 'boolean',
        'distance' => 'decimal:2',
        'warehouse_lat' => 'decimal:8',
        'warehouse_lng' => 'decimal:8',
        'delivery_lat' => 'decimal:8',
        'delivery_lng' => 'decimal:8',
        'metadata' => 'array',
        'alert_sent_at' => 'datetime',
    ];

    // Scopes
    public function scopeWithinRange(Builder $query): Builder
    {
        return $query->where('within_range', true);
    }

    public function scopeOutOfRange(Builder $query): Builder
    {
        return $query->where('within_range', false);
    }

    public function scopeByDelivery(Builder $query, string $deliveryId): Builder
    {
        return $query->where('delivery_id', $deliveryId);
    }

    public function scopeRecentAlerts(Builder $query, int $hours = 24): Builder
    {
        return $query->where('created_at', '>=', Carbon::now()->subHours($hours));
    }

    public function scopeByAlertType(Builder $query, string $type): Builder
    {
        return $query->where('alert_type', $type);
    }

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('status', 'sent');
    }

    // Accessors
    public function getWarehouseCoordsAttribute(): array
    {
        return [(float) $this->warehouse_lat, (float) $this->warehouse_lng];
    }

    public function getDeliveryCoordsAttribute(): array
    {
        return [(float) $this->delivery_lat, (float) $this->delivery_lng];
    }

    public function getFormattedDistanceAttribute(): string
    {
        if ($this->distance >= 1000) {
            return round($this->distance / 1000, 2) . ' km';
        }
        return $this->distance . ' m';
    }

    public function getAlertStatusColorAttribute(): string
    {
        return match($this->status) {
            'sent' => 'success',
            'failed' => 'danger',
            'pending' => 'warning',
            default => 'secondary'
        };
    }

    public function getRangeStatusColorAttribute(): string
    {
        return $this->within_range ? 'success' : 'danger';
    }

    // Static methods for analytics
    public static function getAlertStats(int $days = 7): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        return [
            'total_alerts' => self::where('created_at', '>=', $startDate)->count(),
            'within_range' => self::withinRange()->where('created_at', '>=', $startDate)->count(),
            'out_of_range' => self::outOfRange()->where('created_at', '>=', $startDate)->count(),
            'successful_sends' => self::successful()->where('created_at', '>=', $startDate)->count(),
            'failed_sends' => self::where('status', 'failed')->where('created_at', '>=', $startDate)->count(),
            'average_distance' => self::where('created_at', '>=', $startDate)->avg('distance'),
            'alerts_by_day' => self::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', $startDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date')
                ->map->count
                ->toArray()
        ];
    }

    public static function getDeliveryStats(string $deliveryId): array
    {
        $alerts = self::byDelivery($deliveryId)->get();
        
        return [
            'total_checks' => $alerts->count(),
            'within_range_count' => $alerts->where('within_range', true)->count(),
            'out_of_range_count' => $alerts->where('within_range', false)->count(),
            'average_distance' => $alerts->avg('distance'),
            'min_distance' => $alerts->min('distance'),
            'max_distance' => $alerts->max('distance'),
            'last_check' => $alerts->sortByDesc('created_at')->first()?->created_at,
            'current_status' => $alerts->sortByDesc('created_at')->first()?->within_range ? 'in_range' : 'out_of_range',
        ];
    }

    public static function getHourlyAlertPattern(): array
    {
        return self::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour')
            ->map->count
            ->toArray();
    }
}
