<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\SessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $bookings = Booking::with(['user', 'computer'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderBy('start_at', $status === 'pending' || $status === 'approved' ? 'asc' : 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.bookings.index', compact('bookings', 'status'));
    }

    public function approve(Booking $booking): RedirectResponse
    {
        if ($booking->status !== 'pending') {
            return back()->withErrors(['booking' => 'Only pending bookings can be approved.']);
        }

        $clash = Booking::where('computer_id', $booking->computer_id)
            ->where('status', 'approved')
            ->where('id', '!=', $booking->id)
            ->where('start_at', '<', $booking->end_at)
            ->where('end_at', '>', $booking->start_at)
            ->exists();

        if ($clash) {
            return back()->withErrors(['booking' => 'This overlaps another approved booking on the same PC.']);
        }

        $booking->update(['status' => 'approved']);

        return back()->with('success', 'Booking approved.');
    }

    public function reject(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:255']]);

        if (! $booking->isOpen()) {
            return back()->withErrors(['booking' => 'This booking is already closed.']);
        }

        $booking->update(['status' => 'rejected', 'admin_note' => $data['admin_note'] ?? null]);

        return back()->with('success', 'Booking rejected.');
    }

    public function checkIn(Request $request, Booking $booking, SessionService $sessions): RedirectResponse
    {
        if ($booking->status !== 'approved') {
            return back()->withErrors(['booking' => 'Approve the booking before checking the member in.']);
        }

        $sessions->start($booking->computer, $booking->user, $request->user(), $booking);

        return redirect()->route('admin.attendance.index')->with('success', "{$booking->user->name} checked in on {$booking->computer->name}.");
    }
}
