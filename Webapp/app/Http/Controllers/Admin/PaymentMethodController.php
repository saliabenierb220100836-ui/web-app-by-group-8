<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PaymentMethodController extends Controller
{
    public function index()
    {
        return view('admin.payment-methods.index', ['methods' => PaymentMethod::ordered()->get()]);
    }

    public function create()
    {
        return view('admin.payment-methods.form', ['method' => new PaymentMethod(['type' => 'ewallet', 'is_active' => true, 'sort_order' => 0])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $method = new PaymentMethod($this->validated($request));
        $method->is_active = $request->boolean('is_active');

        if ($request->hasFile('qr')) {
            $method->qr_path = $request->file('qr')->store('payment-qr', 'public');
        }

        $method->save();

        return redirect()->route('admin.payment-methods.index')->with('success', "{$method->name} added.");
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        return view('admin.payment-methods.form', ['method' => $paymentMethod]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->fill($this->validated($request));
        $paymentMethod->is_active = $request->boolean('is_active');

        if ($request->hasFile('qr')) {
            if ($paymentMethod->qr_path) {
                Storage::disk('public')->delete($paymentMethod->qr_path);
            }
            $paymentMethod->qr_path = $request->file('qr')->store('payment-qr', 'public');
        }

        $paymentMethod->save();

        return redirect()->route('admin.payment-methods.index')->with('success', "{$paymentMethod->name} updated.");
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        if ($paymentMethod->qr_path) {
            Storage::disk('public')->delete($paymentMethod->qr_path);
        }

        $paymentMethod->delete();

        return redirect()->route('admin.payment-methods.index')->with('success', 'Payment method removed. Past payments keep their method name.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'type' => ['required', Rule::in(['cash', 'ewallet', 'bank'])],
            'account_name' => ['nullable', 'string', 'max:120'],
            'account_number' => ['nullable', 'string', 'max:60'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'qr' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
    }
}
