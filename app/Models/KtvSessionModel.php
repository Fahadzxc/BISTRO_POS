<?php

namespace App\Models;

use CodeIgniter\Model;

class KtvSessionModel extends Model
{
    protected $table            = 'ktv_sessions';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'room_id',
        'start_time',
        'end_time',
        'paused_at',
        'total_paused_seconds',
        'total_minutes',
        'total_amount',
        'cashier_id',
        'status',
    ];
    protected $useTimestamps    = false;

    /**
     * Get active session for a room.
     */
    public function getActiveByRoom(int $roomId): ?array
    {
        return $this->where('room_id', $roomId)
            ->whereIn('status', ['active', 'paused'])
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * Compute elapsed seconds (excluding paused time).
     */
    public function getElapsedSeconds(array $session): int
    {
        $start = strtotime($session['start_time']);
        $now   = time();
        $paused = (int) ($session['total_paused_seconds'] ?? 0);

        if ($session['status'] === 'paused' && ! empty($session['paused_at'])) {
            $pausedAt = strtotime($session['paused_at']);
            $paused  += $now - $pausedAt;
        }

        return max(0, $now - $start - $paused);
    }

    /**
     * Compute bill: hourly_rate * hours, rounded UP to next full hour.
     */
    public static function computeAmount(float $hourlyRate, int $totalMinutes): float
    {
        $hours = (int) ceil($totalMinutes / 60);
        if ($hours < 1) {
            $hours = 1;
        }
        return round($hours * $hourlyRate, 2);
    }
}
