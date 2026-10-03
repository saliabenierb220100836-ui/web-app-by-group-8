<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $members = User::members()
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.members.index', compact('members', 'search'));
    }

    public function create()
    {
        return view('admin.members.form', [
            'member' => new User,
            'methods' => PaymentMethod::active()->ordered()->get(),
            'accountFee' => Setting::number('account_fee'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'student_id_no' => ['nullable', 'string', 'max:50'],
            'is_student' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', Password::min(8)],
            'collect_fee' => ['nullable', 'boolean'],
            'payment_method_id' => ['nullable', 'integer'],
        ]);

        $plain = $data['password'] ?? null ?: Str::password(10, symbols: false);
        $fee = Setting::number('account_fee');

        $member = DB::transaction(function () use ($request, $data, $plain, $fee) {
            $member = new User(collect($data)->only(['name', 'email', 'phone', 'student_id_no'])->all());
            $member->password = $plain;
            $member->role = 'member';
            $member->is_active = true;
            $member->is_student = $request->boolean('is_student');
            $member->email_verified_at = now();
            $member->save();

            if ($request->boolean('collect_fee') && $fee > 0) {
                $method = PaymentMethod::active()->find($data['payment_method_id'] ?? null);

                Payment::create([
                    'user_id' => $member->id,
                    'payment_method_id' => $method?->id,
                    'purpose' => 'account_fee',
                    'method_name' => $method?->name ?? 'Cash',
                    'amount' => $fee,
                    'status' => 'confirmed',
                    'confirmed_by' => $request->user()->id,
                    'confirmed_at' => now(),
                    'note' => 'Account creation fee',
                ]);
            }

            return $member;
        });

        return redirect()->route('admin.members.index')
            ->with('success', "Account created for {$member->name}.")
            ->with('new_password', ['email' => $member->email, 'password' => $plain]);
    }

    public function edit(User $member)
    {
        abort_unless($member->role === 'member', 404);

        return view('admin.members.form', ['member' => $member, 'methods' => collect(), 'accountFee' => 0]);
    }

    public function update(Request $request, User $member): RedirectResponse
    {
        abort_unless($member->role === 'member', 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($member->id)],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'student_id_no' => ['nullable', 'string', 'max:50'],
            'is_student' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', Password::min(8)],
        ]);

        $member->fill(collect($data)->only(['name', 'email', 'phone', 'student_id_no'])->all());
        $member->is_student = $request->boolean('is_student');
        $member->is_active = $request->boolean('is_active');

        if (! blank($data['password'] ?? null)) {
            $member->password = $data['password'];
        }

        $member->save();

        return redirect()->route('admin.members.index')->with('success', "{$member->name} updated.");
    }
}
