<?php

namespace App\Services;

use App\Models\Computer;
use App\Models\Promo;
use App\Models\Setting;
use App\Models\User;
use Carbon\CarbonInterface;

class PricingService
{
    public function baseRate(Computer $computer): float
    {
        return Setting::number($computer->isVip() ? 'rate_vip' : 'rate_standard');
    }

    /**
     * Cheapest hourly rate this user can get on this PC at the given moment.
     * Promos do NOT stack: the single best (lowest) rate wins.
     *
     * @return array{rate: float, base: float, promo: ?Promo}
     */
    public function bestRate(Computer $computer, ?User $user = null, ?CarbonInterface $at = null): array
    {
        $at ??= now();
        $base = $this->baseRate($computer);
        $best = ['rate' => $base, 'base' => $base, 'promo' => null];

        foreach (Promo::active()->get() as $promo) {
            if (! $this->promoApplies($promo, $computer, $user, $at)) {
                continue;
            }

            $discounted = $this->discountedRate($promo, $base);

            if ($discounted < $best['rate']) {
                $best['rate'] = $discounted;
                $best['promo'] = $promo;
            }
        }

        return $best;
    }

    public function promoApplies(Promo $promo, Computer $computer, ?User $user, CarbonInterface $at): bool
    {
        if ($promo->applies_to !== 'all' && $promo->applies_to !== $computer->type) {
            return false;
        }

        if ($promo->requires_student && ! ($user && $user->is_student)) {
            return false;
        }

        return $this->withinWindow($promo->startHm(), $promo->endHm(), $at);
    }

    /** True when the promo's time window is open right now (ignores student / PC-type rules). */
    public function isLiveNow(Promo $promo, ?CarbonInterface $at = null): bool
    {
        return $promo->is_active && $this->withinWindow($promo->startHm(), $promo->endHm(), $at ?? now());
    }

    /** Supports overnight windows such as 22:00 -> 06:00. A missing start/end means "all day". */
    public function withinWindow(?string $start, ?string $end, CarbonInterface $at): bool
    {
        if (! $start || ! $end) {
            return true;
        }

        $time = $at->format('H:i');

        return $start <= $end
            ? ($time >= $start && $time < $end)
            : ($time >= $start || $time < $end);
    }

    public function discountedRate(Promo $promo, float $base): float
    {
        $value = (float) $promo->discount_value;

        $rate = match ($promo->discount_type) {
            'percent' => $base * (1 - min(max($value, 0), 100) / 100),
            'fixed_rate' => $value,
            default => $base,
        };

        return round(max(0, min($rate, $base)), 2);
    }

    /** Round actual minutes up to the admin-configured billing increment (default 15). */
    public function billedMinutes(int $minutes): int
    {
        $step = max(1, (int) Setting::get('billing_increment_minutes'));

        return (int) (ceil(max($minutes, 1) / $step) * $step);
    }

    public function amountFor(float $rate, int $billedMinutes): float
    {
        return round($rate * $billedMinutes / 60, 2);
    }
}
