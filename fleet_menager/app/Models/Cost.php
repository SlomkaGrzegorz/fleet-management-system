<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cost extends Model
{
    use HasFactory;

    public const CATEGORY_FUEL = 'fuel';
    public const CATEGORY_SERVICE = 'service';
    public const CATEGORY_REPAIR = 'repair';
    public const CATEGORY_INSURANCE = 'insurance';
    public const CATEGORY_TAX = 'tax';
    public const CATEGORY_FINE = 'fine';
    public const CATEGORY_PARTS = 'parts';
    public const CATEGORY_OTHER = 'other';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'vehicle_id',
        'event_id',
        'entered_by',
        'category',
        'amount',
        'incurred_at',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'incurred_at' => 'date',
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

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'event_id', 'event_id');
    }

    public function scopeBetween(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('incurred_at', [$from, $to]);
    }

    public function scopeOfCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }
}
