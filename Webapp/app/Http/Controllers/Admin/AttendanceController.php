<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Computer;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PcSession;
use App\Models\User;
use App\Services\SessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'group' => ['nullable', 'in:log,pc,member'],
            'computer_id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer'],
        ]);

        $group = $request->query('group', 'log');
        $from = $request->query('from', today()->toDateString());
        $to = $request->query('to', today()->toDateString());

        $base = PcSession::query()
            ->whereDate('started_at', '>=', $from)
            ->whereDate('started_at', '<=', $to)
            ->when($request->filled('computer_id'), fn ($q) => $q->where('computer_id', $request->query('computer_id')))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->query('user_id')));

        $totals = (clone $base)->selectRaw('count(*) as sessions, coalesce(sum(billed_minutes),0) as minutes, coalesce(sum(amount),0) as revenue')->first();

        $rows = match ($group) {
            'pc' => (clone $base)->selectRaw('computer_id, count(*) as sessions, coalesce(sum(billed_minutes),0) as minutes, coalesce(sum(amount),0) as revenue')
                ->groupBy('computer_id')->with('computer')->orderByDesc('sessions')->get(),
            'member' => (clone $base)->selectRaw('user_id, count(*) as sessions, coalesce(sum(billed_minutes),0) as minutes, coalesce(sum(amount),0) as revenue')
                ->groupBy('user_id')->with('user')->orderByDesc('sessions')->get(),
            default => (clone $base)->with(['computer', 'user'])->latest('started_at')->paginate(20)->withQueryString(),
        };

        return view('admin.attendance.index', [
            'group' => $group,
            'from' => $from,
            'to' => $to,
            'rows' => $rows,
            'totals' => $totals,
            'active' => PcSession::with(['computer', 'user'])->whereNull('ended_at')->orderBy('started_at')->get(),
            'freePcs' => Computer::where('status', 'available')->orderBy('type')->orderBy('name')->get(),
            'members' => User::members()->active()->orderBy('name')->get(['id', 'name', 'email']),
            'allPcs' => Computer::orderBy('name')->get(['id', 'name']),
            'methods' => PaymentMethod::active()->ordered()->get(),
        ]);
    }

    public function checkIn(Request $request, SessionService $sessions): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'computer_id' => ['required', 'integer', 'exists:computers,id'],
        ]);

        $member = User::members()->findOrFail($data['user_id']);
        $computer = Computer::findOrFail($data['computer_id']);

        $session = $sessions->start($computer, $member, $request->user());

        return back()->with('success', "{$member->name} checked in on {$computer->name} at ".$session->started_at->format('g:i A').'.');
    }

    public function checkOut(Request $request, PcSession $pcSession, SessionService $sessions): RedirectResponse
    {
        $data = $request->validate([
            'payment_method_id' => ['nullable', 'integer'],
            'mark_paid' => ['nullable', 'boolean'],
        ]);

        $session = $sessions->end($pcSession);

        if ($request->boolean('mark_paid') && $session->payment_status === 'unpaid') {
            $method = PaymentMethod::active()->find($data['payment_method_id'] ?? null);

            DB::transaction(function () use ($session, $method, $request) {
                Payment::create([
                    'user_id' => $session->user_id,
                    'pc_session_id' => $session->id,
                    'payment_method_id' => $method?->id,
                    'purpose' => 'session',
                    'method_name' => $method?->name ?? 'Cash',
                    'amount' => $session->amount,
                    'status' => 'confirmed',
                    'confirmed_by' => $request->user()->id,
                    'confirmed_at' => now(),
                ]);

                $session->update(['payment_status' => 'paid']);
            });
        }

        return back()->with('success', sprintf(
            '%s checked out. %d min billed, total ₱%s%s.',
            $session->user->name,
            $session->billed_minutes,
            number_format((float) $session->amount, 2),
            $session->payment_status === 'paid' ? ' (paid)' : ' (unpaid)'
        ));
    }
}
