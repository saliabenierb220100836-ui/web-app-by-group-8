<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/profile')->assertOk();
});

test('member can update name and phone but never role or student status', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch('/profile', [
        'name' => 'Test User',
        'phone' => '0917 123 4567',
        'role' => 'admin',
        'is_student' => 1,
    ])->assertSessionHasNoErrors()->assertRedirect('/profile');

    $user->refresh();

    expect($user->name)->toBe('Test User')
        ->and($user->phone)->toBe('0917 123 4567')
        ->and($user->role)->toBe('member')
        ->and($user->is_student)->toBeFalse();
});
