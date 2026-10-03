<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Computer;
use App\Models\Setting;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    private const MAX_OPEN_BOOKINGS = 3;

    public function index(Request $request)
    {
        $bookings = $request->user()->bookings()->with('computer')->latest('start_at')->paginate(10);

        return view('member.bookings.index', compact('bookings'));
    }

    public function create(Request $request, Computer $computer, PricingService $pricing)
    {
        return view('member.bookings.create', [
            'computer' => $computer,
            'price' => $pricing->bestRate($computer, $request->user()),
            'maxHours' => (int) Setting::get('booking_max_hours'),
            'minAdvance' => $computer->isVip() ? (int) Setting::get('vip_min_advance_minutes') : 0,
            'upcoming' => Booking::where('computer_id', $computer->id)->whereIn('status', ['pending', 'approved'])->where('end_at', '>', now())->orderBy('start_at')->limit(6)->get(),
        ]);
    }

    public function store(Request $request, Computer $computer, PricingService $pricing): RedirectResponse
    {
        $maxHours = (int) Setting::get('booking_max_hours');

        $data = $request->validate([
            'start_at' => ['required', 'date'],
            'hours' => ['required', 'integer', 'min:1', 'max:'.$maxHours],
        ]);

        $user = $request->user();
        $start = Carbon::parse($data['start_at'])->second(0);
        $end = $start->copy()->addHours((int) $data['hours']);

        if ($computer->status === 'maintenance') {
            throw ValidationException::withMessages(['start_at' => "{$computer->name} is under maintenance and can't be booked."]);
        }

        if ($start->lt(now()->subMinutes(5))) {
            throw ValidationException::withMessages(['start_at' => 'That start time is already in the past.']);
        }

        if ($start->gt(now()->addDays(14))) {
            throw ValidationException::withMessages(['start_at' => 'You can only book up to 14 days ahead.']);
        }

        if ($computer->isVip()) {
            $minAdvance = (int) Setting::get('vip_min_advance_minutes');

            if ($start->lt(now()->addMinutes($minAdvance))) {
                throw ValidationException::withMessages(['start_at' => "VIP PCs need advance booking: start at least {$minAdvance} minutes from now."]);
            }
        }

        $overlap = Booking::where('computer_id', $computer->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages(['start_at' => 'That PC is already booked for part of this time. Pick another slot or another PC.']);
        }

        $openCount = Booking::where('user_id', $user->id)->whereIn('status', ['pending', 'approved'])->where('end_at', '>', now())->count();

        if ($openCount >= self::MAX_OPEN_BOOKINGS) {
            throw ValidationException::withMessages(['start_at' => 'You already have '.self::MAX_OPEN_BOOKINGS.' upcoming bookings. Cancel one or wait until it is done.']);
        }

        $rate = $pricing->bestRate($computer, $user, $start)['rate'];

        Booking::create([
            'user_id' => $user->id,
            'computer_id' => $computer->id,
            'start_at' => $start,
            'end_at' => $end,
            'hours' => (int) $data['hours'],
            'estimated_amount' => round($rate * (int) $data['hours'], 2),
            'status' => 'pending',
        ]);

        return redirect()->route('bookings.index')->with('success', "Booking request sent for {$computer->name}. The front desk will approve it shortly.");
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 404);

        if (! $booking->isOpen()) {
            return back()->withErrors(['booking' => 'Only pending or approved bookings can be cancelled.']);
        }

        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking cancelled.');
    }
}
