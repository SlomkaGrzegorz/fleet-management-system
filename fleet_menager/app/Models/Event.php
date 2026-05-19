<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Zdarzenie związane z pojazdem: serwis, przegląd, ubezpieczenie,
 * incydent/wypadek. Pełni rolę kotwicy dla kosztów i dokumentów.
 */
class Event extends Model
{
    use HasFactory;

    public const TYPE_INSURANCE = 'insurance';
    public const TYPE_INSPECTION = 'inspection';
    public const TYPE_SERVICE = 'service';
    public const TYPE_REPAIR = 'repair';
    public const TYPE_INCIDENT = 'incident';
    public const TYPE_OTHER = 'other';

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_CLOSED = 'closed';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'vehicle_id',
        'reported_by',
        'type',
        'event_date',
        'expiry_date',
        'notes',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function costs(): HasMany
    {
        return $this->hasMany(Cost::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function scopeIncidents(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_INCIDENT);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_OPEN, self::STATUS_IN_PROGRESS]);
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [
                now()->toDateString(),
                now()->addDays($days)->toDateString(),
            ]);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now()->toDateString())
            ->where('status', '!=', self::STATUS_CLOSED);
    }
}
