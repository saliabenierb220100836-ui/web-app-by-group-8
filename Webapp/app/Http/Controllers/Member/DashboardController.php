<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Computer;
use App\Models\PcSession;
use App\Models\Promo;
use App\Services\PricingService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, PricingService $pricing)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $type = in_array($request->query('type'), ['standard', 'vip'], true) ? $request->query('type') : null;

        $computers = Computer::with('activeSession')
            ->when($type, fn ($q) => $q->where('type', $type))
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $upcoming = Booking::whereIn('computer_id', $computers->pluck('id'))
            ->whereIn('status', ['pending', 'approved'])
            ->where('end_at', '>', now())
            ->orderBy('start_at')
            ->get()
            ->groupBy('computer_id');

        $cards = $computers->map(function (Computer $pc) use ($pricing, $user, $upcoming) {
            $bookings = $upcoming->get($pc->id, collect());
            $reservedNow = $bookings->first(fn ($b) => $b->status === 'approved' && $b->start_at <= now() && $b->end_at > now());

            $state = match (true) {
                $pc->status === 'maintenance' => 'maintenance',
                $pc->activeSession !== null || $pc->status === 'in_use' => 'in_use',
                $reservedNow !== null => 'reserved',
                default => 'available',
            };

            return [
                'pc' => $pc,
                'state' => $state,
                'price' => $pricing->bestRate($pc, $user),
                'next_booking' => $bookings->first(),
            ];
        });

        $counts = [
            'standard' => Computer::where('type', 'standard')->where('status', 'available')->count(),
            'vip' => Computer::where('type', 'vip')->where('status', 'available')->count(),
        ];

        $promos = Promo::active()->ordered()->get()->map(fn ($p) => [
            'promo' => $p,
            'live' => $pricing->isLiveNow($p),
        ]);

        $amountDue = (float) PcSession::where('user_id', $user->id)->whereNotNull('ended_at')->where('payment_status', 'unpaid')->sum('amount');
        $activeSession = PcSession::with('computer')->where('user_id', $user->id)->whereNull('ended_at')->first();
        $nextBooking = Booking::with('computer')->where('user_id', $user->id)->whereIn('status', ['pending', 'approved'])->where('end_at', '>', now())->orderBy('start_at')->first();

        return view('member.dashboard', compact('cards', 'type', 'counts', 'promos', 'amountDue', 'activeSession', 'nextBooking'));
    }
}
