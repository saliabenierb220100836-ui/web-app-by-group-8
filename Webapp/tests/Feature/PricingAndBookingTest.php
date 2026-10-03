<?php

use App\Models\Booking;
use App\Models\Computer;
use App\Models\PcSession;
use App\Models\User;
use App\Services\PricingService;
use App\Services\SessionService;
use Carbon\Carbon;

beforeEach(function () {
    $this->seed();
    $this->pricing = app(PricingService::class);
    $this->standard = Computer::where('name', 'PC-01')->first();
    $this->vip = Computer::where('name', 'VIP-01')->first();
});

afterEach(fn () => Carbon::setTestNow());

test('base rates are 25 standard and 40 VIP', function () {
    Carbon::setTestNow('2026-10-05 14:00:00');
    $member = User::factory()->create();

    expect($this->pricing->bestRate($this->standard, $member)['rate'])->toBe(25.0)
        ->and($this->pricing->bestRate($this->vip, $member)['rate'])->toBe(40.0);
});

test('night promo applies across midnight only', function () {
    $member = User::factory()->create();

    Carbon::setTestNow('2026-10-05 23:30:00');
    expect($this->pricing->bestRate($this->standard, $member)['promo']?->title)->toBe('Night Owl Promo');

    Carbon::setTestNow('2026-10-06 02:00:00');
    expect($this->pricing->bestRate($this->standard, $member)['promo']?->title)->toBe('Night Owl Promo');

    Carbon::setTestNow('2026-10-06 12:00:00');
    expect($this->pricing->bestRate($this->standard, $member)['promo'])->toBeNull();
});

test('student promo needs a verified student', function () {
    Carbon::setTestNow('2026-10-05 14:00:00');

    $normal = User::factory()->create();
    $student = User::factory()->student()->create();

    expect($this->pricing->bestRate($this->standard, $normal)['promo'])->toBeNull()
        ->and($this->pricing->bestRate($this->standard, $student)['rate'])->toBe(22.5);
});

test('billing rounds up to the 15 minute block', function () {
    expect($this->pricing->billedMinutes(1))->toBe(15)
        ->and($this->pricing->billedMinutes(20))->toBe(30)
        ->and($this->pricing->billedMinutes(60))->toBe(60)
        ->and($this->pricing->amountFor(25.0, 60))->toBe(25.0)
        ->and($this->pricing->amountFor(40.0, 30))->toBe(20.0);
});

test('check-in and check-out bill the member and free the PC', function () {
    Carbon::setTestNow('2026-10-05 14:00:00');

    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();
    $sessions = app(SessionService::class);

    $session = $sessions->start($this->standard, $member, $admin);
    expect($this->standard->fresh()->status)->toBe('in_use');

    Carbon::setTestNow('2026-10-05 15:00:00');
    $session = $sessions->end($session);

    expect((float) $session->amount)->toBe(25.0)
        ->and($session->billed_minutes)->toBe(60)
        ->and($this->standard->fresh()->status)->toBe('available');
});

test('VIP booking must be made in advance', function () {
    Carbon::setTestNow('2026-10-05 14:00:00');
    $member = User::factory()->create();

    $this->actingAs($member)->post("/pcs/{$this->vip->id}/book", [
        'start_at' => '2026-10-05T14:10', 'hours' => 1,
    ])->assertSessionHasErrors('start_at');

    $this->actingAs($member)->post("/pcs/{$this->vip->id}/book", [
        'start_at' => '2026-10-05T16:00', 'hours' => 2,
    ])->assertSessionHasNoErrors();

    $booking = Booking::first();
    expect($booking->status)->toBe('pending')->and((float) $booking->estimated_amount)->toBe(80.0);
});

test('overlapping bookings on the same PC are rejected', function () {
    Carbon::setTestNow('2026-10-05 14:00:00');
    $a = User::factory()->create();
    $b = User::factory()->create();

    $this->actingAs($a)->post("/pcs/{$this->standard->id}/book", ['start_at' => '2026-10-05T16:00', 'hours' => 2])->assertSessionHasNoErrors();
    $this->actingAs($b)->post("/pcs/{$this->standard->id}/book", ['start_at' => '2026-10-05T17:00', 'hours' => 1])->assertSessionHasErrors('start_at');
});

test('a member cannot pay or cancel another member\'s items', function () {
    Carbon::setTestNow('2026-10-05 14:00:00');
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $booking = Booking::create(['user_id' => $owner->id, 'computer_id' => $this->standard->id, 'start_at' => now()->addHour(), 'end_at' => now()->addHours(2), 'hours' => 1, 'estimated_amount' => 25, 'status' => 'pending']);
    $this->actingAs($other)->post("/bookings/{$booking->id}/cancel")->assertNotFound();

    $session = PcSession::create(['computer_id' => $this->standard->id, 'user_id' => $owner->id, 'started_at' => now()->subHour(), 'ended_at' => now(), 'rate_applied' => 25, 'minutes' => 60, 'billed_minutes' => 60, 'amount' => 25]);
    $cash = \App\Models\PaymentMethod::where('type', 'cash')->first();
    $this->actingAs($other)->post('/payments', ['pc_session_id' => $session->id, 'payment_method_id' => $cash->id])->assertSessionHasErrors('pc_session_id');
});
