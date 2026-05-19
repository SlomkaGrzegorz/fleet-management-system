<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    use HasFactory;

    public const TYPE_EXPIRY_WARNING = 'expiry_warning';
    public const TYPE_OVERDUE = 'overdue';
    public const TYPE_REMINDER = 'reminder';
    public const TYPE_INCIDENT = 'incident';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'vehicle_id',
        'event_id',
        'type',
        'trigger_date',
        'dismissed',
        'sent_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trigger_date' => 'date',
            'dismissed' => 'boolean',
            'sent_at' => 'datetime',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('dismissed', false);
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->where('trigger_date', '<=', now()->toDateString());
    }
}
