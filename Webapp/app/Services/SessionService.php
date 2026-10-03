<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Computer;
use App\Models\PcSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SessionService
{
    public function __construct(private PricingService $pricing) {}

    public function start(Computer $computer, User $member, User $admin, ?Booking $booking = null): PcSession
    {
        return DB::transaction(function () use ($computer, $member, $admin, $booking) {
            // Lock the row so two admins can't seat two people at one PC.
            $computer = Computer::query()->lockForUpdate()->findOrFail($computer->id);

            if ($computer->status === 'maintenance') {
                throw ValidationException::withMessages(['computer_id' => "{$computer->name} is under maintenance."]);
            }

            if ($computer->status === 'in_use') {
                throw ValidationException::withMessages(['computer_id' => "{$computer->name} is already in use."]);
            }

            if (! $member->is_active || $member->role !== 'member') {
                throw ValidationException::withMessages(['user_id' => 'That account is not an active member.']);
            }

            if (PcSession::where('user_id', $member->id)->whereNull('ended_at')->exists()) {
                throw ValidationException::withMessages(['user_id' => "{$member->name} is already checked in on another PC."]);
            }

            $best = $this->pricing->bestRate($computer, $member, now());

            $session = PcSession::create([
                'computer_id' => $computer->id,
                'user_id' => $member->id,
                'booking_id' => $booking?->id,
                'started_at' => now(),
                'rate_applied' => $best['rate'],
                'promo_name' => $best['promo']?->title,
                'checked_in_by' => $admin->id,
            ]);

            $computer->update(['status' => 'in_use']);
            $booking?->update(['status' => 'completed']);

            return $session;
        });
    }

    public function end(PcSession $session): PcSession
    {
        return DB::transaction(function () use ($session) {
            $session = PcSession::query()->lockForUpdate()->findOrFail($session->id);

            if ($session->ended_at) {
                return $session;
            }

            $end = now();
            $minutes = (int) max(1, ceil(($end->getTimestamp() - $session->started_at->getTimestamp()) / 60));
            $billed = $this->pricing->billedMinutes($minutes);

            $session->update([
                'ended_at' => $end,
                'minutes' => $minutes,
                'billed_minutes' => $billed,
                'amount' => $this->pricing->amountFor((float) $session->rate_applied, $billed),
            ]);

            Computer::whereKey($session->computer_id)->where('status', 'in_use')->update(['status' => 'available']);

            return $session;
        });
    }
}
