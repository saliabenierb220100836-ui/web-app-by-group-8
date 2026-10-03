<?php

test('the home page sends visitors to the login screen', function () {
    $this->get('/')->assertRedirect('/login');
});

test('the promo page is public', function () {
    $this->seed();

    $this->get('/promos')->assertOk()->assertSee('Night Owl Promo')->assertSee('Student Promo');
});
