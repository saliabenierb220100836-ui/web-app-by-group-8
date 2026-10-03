<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    private const KEYS = ['account_fee', 'rate_standard', 'rate_vip', 'vip_min_advance_minutes', 'booking_max_hours', 'billing_increment_minutes'];

    public function edit()
    {
        $values = [];

        foreach (self::KEYS as $key) {
            $values[$key] = Setting::get($key);
        }

        return view('admin.pricing', compact('values'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'account_fee' => ['required', 'numeric', 'min:0', 'max:100000'],
            'rate_standard' => ['required', 'numeric', 'min:0', 'max:100000'],
            'rate_vip' => ['required', 'numeric', 'min:0', 'max:100000'],
            'vip_min_advance_minutes' => ['required', 'integer', 'min:0', 'max:10080'],
            'booking_max_hours' => ['required', 'integer', 'min:1', 'max:24'],
            'billing_increment_minutes' => ['required', 'integer', 'min:1', 'max:60'],
        ]);

        foreach (self::KEYS as $key) {
            Setting::put($key, $data[$key]);
        }

        return back()->with('success', 'Pricing updated. Sessions already running keep the rate they started with.');
    }
}
