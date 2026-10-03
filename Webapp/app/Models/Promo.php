<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $fillable = [
        'title', 'tagline', 'description', 'badge_label', 'discount_type', 'discount_value',
        'applies_to', 'start_time', 'end_time', 'requires_student', 'terms', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'requires_student' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? asset('storage/'.$this->image_path) : null;
    }

    /** "22:00" style, trimmed from MySQL's "22:00:00". */
    public function startHm(): ?string
    {
        return $this->start_time ? substr($this->start_time, 0, 5) : null;
    }

    public function endHm(): ?string
    {
        return $this->end_time ? substr($this->end_time, 0, 5) : null;
    }

    public function discountLabel(): string
    {
        return match ($this->discount_type) {
            'percent' => rtrim(rtrim(number_format((float) $this->discount_value, 2), '0'), '.').'% off',
            'fixed_rate' => '₱'.rtrim(rtrim(number_format((float) $this->discount_value, 2), '0'), '.').'/hr',
            default => 'Special offer',
        };
    }
}
