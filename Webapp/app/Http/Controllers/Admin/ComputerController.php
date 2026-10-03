<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Computer;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ComputerController extends Controller
{
    public function index()
    {
        return view('admin.computers.index', [
            'computers' => Computer::orderBy('type')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.computers.form', ['computer' => new Computer(['type' => 'standard', 'status' => 'available'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $computer = new Computer($data);

        if ($request->hasFile('image')) {
            $computer->image_path = $request->file('image')->store('pcs', 'public');
        }

        $computer->save();

        return redirect()->route('admin.computers.index')->with('success', "{$computer->name} added.");
    }

    public function edit(Computer $computer)
    {
        return view('admin.computers.form', compact('computer'));
    }

    public function update(Request $request, Computer $computer): RedirectResponse
    {
        $data = $this->validated($request, $computer);

        // Don't let an admin flip a PC that has someone seated to "available" by hand.
        if ($computer->activeSession()->exists()) {
            $data['status'] = 'in_use';
        }

        $computer->fill($data);

        if ($request->hasFile('image')) {
            if ($computer->image_path) {
                Storage::disk('public')->delete($computer->image_path);
            }
            $computer->image_path = $request->file('image')->store('pcs', 'public');
        }

        $computer->save();

        return redirect()->route('admin.computers.index')->with('success', "{$computer->name} updated.");
    }

    public function destroy(Computer $computer): RedirectResponse
    {
        try {
            $path = $computer->image_path;
            $computer->delete();

            if ($path) {
                Storage::disk('public')->delete($path);
            }
        } catch (QueryException) {
            return back()->withErrors(['computer' => "{$computer->name} has attendance history and can't be deleted. Set it to Maintenance instead."]);
        }

        return redirect()->route('admin.computers.index')->with('success', 'PC removed.');
    }

    private function validated(Request $request, ?Computer $computer = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('computers', 'name')->ignore($computer?->id)],
            'type' => ['required', Rule::in(['standard', 'vip'])],
            'status' => ['required', Rule::in(['available', 'maintenance'])], // in_use is set only by check-in/out
            'specs' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);
    }
}
