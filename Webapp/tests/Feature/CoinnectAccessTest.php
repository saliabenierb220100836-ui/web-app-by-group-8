<?php

use App\Models\User;

test('guests are sent to login', function () {
    $this->get('/dashboard')->assertRedirect('/login');
    $this->get('/admin')->assertRedirect('/login');
});

test('members get a 404 on every admin page', function () {
    $member = User::factory()->create();

    foreach (['/admin', '/admin/members', '/admin/pricing', '/admin/computers', '/admin/payments'] as $url) {
        $this->actingAs($member)->get($url)->assertNotFound();
    }
});

test('admins can open the admin panel', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin')->assertOk();
    $this->actingAs($admin)->get('/admin/attendance')->assertOk();
    $this->actingAs($admin)->get('/admin/promos')->assertOk();
});

test('admins logging in land on the admin panel', function () {
    $admin = User::factory()->admin()->create();

    $this->post('/login', ['email' => $admin->email, 'password' => 'password'])->assertRedirect('/admin');
});

test('deactivated accounts cannot log in', function () {
    $user = User::factory()->inactive()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'password']);

    $this->assertGuest();
});

test('member dashboard shows the PC feed', function () {
    $this->seed();

    $member = User::factory()->create();

    $this->actingAs($member)->get('/dashboard')->assertOk()->assertSee('PC-01')->assertSee('VIP-10');
});

test('seeding creates 20 standard and 10 VIP PCs', function () {
    $this->seed();

    expect(\App\Models\Computer::where('type', 'standard')->count())->toBe(20)
        ->and(\App\Models\Computer::where('type', 'vip')->count())->toBe(10);
});
