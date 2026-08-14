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
}
