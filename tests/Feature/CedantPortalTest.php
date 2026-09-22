<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('cedant pages require authentication', function () {
    foreach (['dashboard', 'policies', 'statements', 'claims', 'help'] as $page) {
        $this->get('/client/'.$page)->assertRedirect('/login');
    }
});

test('users without the client role cannot access cedant pages', function () {
    $this->actingAs(User::factory()->create());
    foreach (['dashboard', 'policies', 'statements', 'claims', 'help'] as $page) {
        $this->get('/client/'.$page)->assertForbidden();
    }
});

test('clients can open their portal and starter sections', function () {
    Role::create(['name' => 'client', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('client');
    $this->actingAs($user);
    $this->get('/client/dashboard')->assertOk()->assertSee('My Policies / Covers')->assertSee('Help &amp; Feedback', false);
    foreach (['policies', 'statements', 'claims', 'help', 'profile'] as $page) {
        $this->get('/client/'.$page)->assertOk();
    }
});

test('authentication pages use cedant wording', function () {
    $this->get('/login')->assertOk()->assertSee('Welcome to your cedant portal');
    $this->get('/register')->assertOk()->assertSee('Create your cedant account');
});
