<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Computer;
use App\Models\Payment;
use App\Models\PcSession;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $computers = Computer::with('activeSession.user')->orderBy('type')->orderBy('name')->get();

        return view('admin.dashboard', [
            'computers' => $computers,
            'stats' => [
                'in_use' => $computers->where('status', 'in_use')->count(),
                'available' => $computers->where('status', 'available')->count(),
                'maintenance' => $computers->where('status', 'maintenance')->count(),
                'revenue_today' => (float) Payment::where('status', 'confirmed')->whereDate('confirmed_at', today())->sum('amount'),
                'pending_bookings' => Booking::where('status', 'pending')->count(),
                'pending_payments' => Payment::where('status', 'pending')->count(),
                'members' => User::members()->active()->count(),
                'sessions_today' => PcSession::whereDate('started_at', today())->count(),
            ],
            'active' => PcSession::with(['computer', 'user'])->whereNull('ended_at')->orderBy('started_at')->get(),
        ]);
    }
}
