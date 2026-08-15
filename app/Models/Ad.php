<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public const DAILY_LIMIT = 2;

    public const OWNER_STATUS_TRANSITIONS = [
        'approved' => ['paused', 'sold'],
        'paused' => ['approved', 'sold'],
    ];

    public const SORTS = [
        'newest',
        'price_asc',
        'price_desc',
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

    /**
     * @param  Builder<Ad>  $query
     * @param  array{q?: string|null, category?: string|null, location?: string|null, min_price?: mixed, max_price?: mixed}  $filters
     * @return Builder<Ad>
     */
    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['q'] ?? null, function (Builder $query, string $term) {
                $like = '%'.$this->escapeLike($term).'%';

                $query->where(function (Builder $inner) use ($like) {
                    $inner->where('title', 'like', $like)
                        ->orWhere('description', 'like', $like);
                });
            })
            ->when($filters['category'] ?? null, fn (Builder $query, string $category) => $query->where('category', $category))
            ->when($filters['location'] ?? null, function (Builder $query, string $location) {
                $query->where('location', 'like', $this->escapeLike($location).'%');
            })
            ->when($this->filledFilter($filters['min_price'] ?? null), fn (Builder $query) => $query->where('price', '>=', $filters['min_price']))
            ->when($this->filledFilter($filters['max_price'] ?? null), fn (Builder $query) => $query->where('price', '<=', $filters['max_price']));
    }

    /**
     * @param  Builder<Ad>  $query
     * @return Builder<Ad>
     */
    public function scopeSorted(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'price_asc' => $query->orderBy('price')->orderByDesc('created_at'),
            'price_desc' => $query->orderByDesc('price')->orderByDesc('created_at'),
            default => $query->latest(),
        };
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

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    private function filledFilter(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }
}
