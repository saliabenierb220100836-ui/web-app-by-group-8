<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'recentSessions' => $user->pcSessions()->with('computer')->latest('started_at')->limit(8)->get(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        // is_student is intentionally NOT editable here; an admin verifies the student ID in person.
        $user->fill(collect($data)->only(['name', 'phone', 'student_id_no'])->all());
        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Profile updated.');
    }
}
