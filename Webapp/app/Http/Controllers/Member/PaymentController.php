<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PcSession;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $unpaid = PcSession::with(['computer', 'payments'])
            ->where('user_id', $user->id)
            ->whereNotNull('ended_at')
            ->where('payment_status', 'unpaid')
            ->latest('started_at')
            ->get();

        return view('member.payments', [
            'unpaid' => $unpaid,
            'methods' => PaymentMethod::active()->ordered()->get(),
            'history' => Payment::with('pcSession.computer')->where('user_id', $user->id)->latest()->paginate(10),
            'accountFee' => Setting::number('account_fee'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pc_session_id' => ['required', 'integer'],
            'payment_method_id' => ['required', 'integer'],
            'reference_no' => ['nullable', 'string', 'max:60'],
            'proof' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $user = $request->user();

        // Scoped to the logged-in member, so another member's session id simply isn't found.
        $session = PcSession::where('user_id', $user->id)->whereNotNull('ended_at')->where('payment_status', 'unpaid')->find($data['pc_session_id']);
        $method = PaymentMethod::active()->find($data['payment_method_id']);

        if (! $session || ! $method) {
            throw ValidationException::withMessages(['pc_session_id' => 'That bill or payment method is not available.']);
        }

        if (! $method->isCash() && blank($data['reference_no'] ?? null)) {
            throw ValidationException::withMessages(['reference_no' => "Enter the reference number from your {$method->name} receipt."]);
        }

        if (Payment::where('pc_session_id', $session->id)->where('status', 'pending')->exists()) {
            throw ValidationException::withMessages(['pc_session_id' => 'You already submitted a payment for this bill. Please wait for the front desk to confirm it.']);
        }

        Payment::create([
            'user_id' => $user->id,
            'pc_session_id' => $session->id,
            'payment_method_id' => $method->id,
            'purpose' => 'session',
            'method_name' => $method->name,
            'amount' => $session->amount,
            'reference_no' => $data['reference_no'] ?? null,
            'proof_path' => $request->hasFile('proof') ? $request->file('proof')->store('payment-proofs', 'local') : null, // private disk
            'status' => 'pending',
        ]);

        return back()->with('success', $method->isCash()
            ? 'Noted. Please pay the cashier at the counter and they will confirm it.'
            : 'Payment submitted. It will show as paid once the front desk confirms it.');
    }
}
