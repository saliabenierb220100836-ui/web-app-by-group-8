<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $payments = Payment::with(['user', 'pcSession.computer'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.payments.index', compact('payments', 'status'));
    }

    public function confirm(Request $request, Payment $payment): RedirectResponse
    {
        if ($payment->status !== 'pending') {
            return back()->withErrors(['payment' => 'This payment was already reviewed.']);
        }

        DB::transaction(function () use ($payment, $request) {
            $payment->update([
                'status' => 'confirmed',
                'confirmed_by' => $request->user()->id,
                'confirmed_at' => now(),
            ]);

            $payment->pcSession?->update(['payment_status' => 'paid']);
        });

        return back()->with('success', 'Payment confirmed.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $data = $request->validate(['note' => ['nullable', 'string', 'max:255']]);

        if ($payment->status !== 'pending') {
            return back()->withErrors(['payment' => 'This payment was already reviewed.']);
        }

        $payment->update([
            'status' => 'rejected',
            'note' => $data['note'] ?? null,
            'confirmed_by' => $request->user()->id,
            'confirmed_at' => now(),
        ]);

        return back()->with('success', 'Payment rejected. The member can submit again.');
    }

    /** Proof screenshots live on the private disk and are only reachable through this admin route. */
    public function proof(Payment $payment)
    {
        $disk = Storage::disk('local');

        abort_unless($payment->proof_path && $disk->exists($payment->proof_path), 404);

        return $disk->response($payment->proof_path);
    }
}
