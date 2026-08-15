<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ad extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'sale',
        'services',
    ];

    public const OWNER_STATUS_TRANSITIONS = [
        'approved' => ['paused', 'sold'],
        'paused' => ['approved', 'sold'],
    ];

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'category',
        'location',
        'contact_info',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(AdImage::class);
    }

    public function translatedCategory(): string
    {
        return __('categories.'.$this->category);
    }

    public function translatedStatus(): string
    {
        return __('status.'.$this->status);
    }

    /**
     * @return list<string>
     */
    public function allowedOwnerStatuses(): array
    {
        return self::OWNER_STATUS_TRANSITIONS[$this->status] ?? [];
    }

    public function ownerCanTransitionTo(string $status): bool
    {
        return in_array($status, $this->allowedOwnerStatuses(), true);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'approved' => 'text-bg-success',
            'pending' => 'text-bg-warning',
            'rejected' => 'text-bg-danger',
            'paused' => 'text-bg-secondary',
            'sold' => 'text-bg-dark',
            default => 'text-bg-secondary',
        };
    }

    public function contactEmail(): string
    {
        return $this->contactParts()['email'];
    }

    public function contactPhone(): string
    {
        return $this->contactParts()['phone'];
    }

    /**
     * @return array{email: string, phone: string}
     */
    public function contactParts(): array
    {
        $parts = array_map('trim', explode(' · ', (string) $this->contact_info, 2));

        return [
            'email' => $parts[0] ?? '',
            'phone' => $parts[1] ?? '',
        ];
    }
}
