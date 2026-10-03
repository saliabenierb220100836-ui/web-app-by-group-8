<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Computer extends Model
{
    protected $fillable = ['name', 'type', 'status', 'specs'];

    public function isVip(): bool
    {
        return $this->type === 'vip';
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? asset('storage/'.$this->image_path) : null;
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(PcSession::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function activeSession(): HasOne
    {
        return $this->hasOne(PcSession::class)->whereNull('ended_at')->latest('started_at');
    }
}
