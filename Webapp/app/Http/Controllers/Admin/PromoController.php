<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PromoController extends Controller
{
    public function index()
    {
        return view('admin.promos.index', ['promos' => Promo::ordered()->get()]);
    }

    public function create()
    {
        return view('admin.promos.form', ['promo' => new Promo(['discount_type' => 'percent', 'applies_to' => 'all', 'is_active' => true, 'sort_order' => 0])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $promo = new Promo($this->validated($request));
        $promo->requires_student = $request->boolean('requires_student');
        $promo->is_active = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $promo->image_path = $request->file('image')->store('promos', 'public');
        }

        $promo->save();

        return redirect()->route('admin.promos.index')->with('success', "Promo \"{$promo->title}\" created.");
    }

    public function edit(Promo $promo)
    {
        return view('admin.promos.form', compact('promo'));
    }

    public function update(Request $request, Promo $promo): RedirectResponse
    {
        $promo->fill($this->validated($request));
        $promo->requires_student = $request->boolean('requires_student');
        $promo->is_active = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            if ($promo->image_path) {
                Storage::disk('public')->delete($promo->image_path);
            }
            $promo->image_path = $request->file('image')->store('promos', 'public');
        }

        $promo->save();

        return redirect()->route('admin.promos.index')->with('success', "Promo \"{$promo->title}\" updated.");
    }

    public function destroy(Promo $promo): RedirectResponse
    {
        if ($promo->image_path) {
            Storage::disk('public')->delete($promo->image_path);
        }

        $promo->delete();

        return redirect()->route('admin.promos.index')->with('success', 'Promo deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'badge_label' => ['nullable', 'string', 'max:40'],
            'discount_type' => ['required', Rule::in(['none', 'percent', 'fixed_rate'])],
            'discount_value' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'applies_to' => ['required', Rule::in(['all', 'standard', 'vip'])],
            'start_time' => ['nullable', 'date_format:H:i', 'required_with:end_time'],
            'end_time' => ['nullable', 'date_format:H:i', 'required_with:start_time'],
            'terms' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        if ($data['discount_type'] === 'percent' && (float) ($data['discount_value'] ?? 0) > 100) {
            throw ValidationException::withMessages(['discount_value' => 'A percentage discount cannot be more than 100.']);
        }

        $data['discount_value'] = $data['discount_value'] ?? 0;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        unset($data['image']);

        return $data;
    }
}
