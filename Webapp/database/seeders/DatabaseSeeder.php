<?php

namespace Database\Seeders;

use App\Models\Computer;
use App\Models\PaymentMethod;
use App\Models\Promo;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Safe to run more than once: it only creates what is missing and never overwrites
 * prices, promos or the admin password that you have already changed.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAdmin();
        $this->seedSettings();
        $this->seedComputers();
        $this->seedPaymentMethods();
        $this->seedPromos();
    }

    private function seedAdmin(): void
    {
        $email = strtolower((string) env('ADMIN_EMAIL', 'admin@coinnect.local'));

        if (User::where('email', $email)->exists()) {
            return;
        }

        $password = env('ADMIN_PASSWORD') ?: Str::password(16, symbols: false);

        $admin = new User(['name' => env('ADMIN_NAME', 'Coinnect Admin'), 'email' => $email]);
        $admin->password = $password;
        $admin->role = 'admin';
        $admin->is_active = true;
        $admin->email_verified_at = now();
        $admin->save();

        $this->command?->warn("Admin created: {$email}");

        if (! env('ADMIN_PASSWORD')) {
            $this->command?->warn("Generated password (shown once, change it after login): {$password}");
        }
    }

    private function seedSettings(): void
    {
        foreach (Setting::DEFAULTS as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    private function seedComputers(): void
    {
        foreach (range(1, 20) as $n) {
            Computer::firstOrCreate(['name' => sprintf('PC-%02d', $n)], ['type' => 'standard', 'status' => 'available']);
        }

        foreach (range(1, 10) as $n) {
            Computer::firstOrCreate(['name' => sprintf('VIP-%02d', $n)], ['type' => 'vip', 'status' => 'available']);
        }
    }

    private function seedPaymentMethods(): void
    {
        // Only Cash starts active. E-wallets/banks stay hidden until the admin enters real account details.
        $rows = [
            ['Cash', 'cash', true, 0, 'Pay at the counter. The front desk will confirm it.'],
            ['GCash', 'ewallet', false, 1, 'Send the exact amount, then enter the reference number from your receipt.'],
            ['Maya', 'ewallet', false, 2, 'Send the exact amount, then enter the reference number from your receipt.'],
            ['Bank Transfer', 'bank', false, 3, 'Transfer the exact amount, then enter the reference number from your receipt.'],
        ];

        foreach ($rows as [$name, $type, $active, $order, $instructions]) {
            PaymentMethod::firstOrCreate(['name' => $name], [
                'type' => $type, 'is_active' => $active, 'sort_order' => $order, 'instructions' => $instructions,
            ]);
        }
    }

    private function seedPromos(): void
    {
        // PLACEHOLDER discounts: edit or replace them in Admin > Promos to match your real offers.
        Promo::firstOrCreate(['title' => 'Night Owl Promo'], [
            'tagline' => 'Play through the night for less',
            'description' => 'Cheaper hourly rate for late-night gamers.',
            'badge_label' => 'NIGHT',
            'discount_type' => 'percent',
            'discount_value' => 20,
            'applies_to' => 'all',
            'start_time' => '22:00',
            'end_time' => '06:00',
            'requires_student' => false,
            'terms' => 'Applies to sessions started between 10:00 PM and 6:00 AM. Cannot be combined with other promos.',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Promo::firstOrCreate(['title' => 'Student Promo'], [
            'tagline' => 'Show your school ID, save every hour',
            'description' => 'Verified students get a discount on every session.',
            'badge_label' => 'STUDENT',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'applies_to' => 'standard',
            'requires_student' => true,
            'terms' => 'Present a valid student ID at the front desk once so staff can verify your account. Cannot be combined with other promos.',
            'is_active' => true,
            'sort_order' => 2,
        ]);
    }
}
