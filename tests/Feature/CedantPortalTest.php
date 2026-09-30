<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('cedant pages require authentication', function () {
    foreach (['dashboard', 'policies', 'statements', 'claims', 'help', 'premium-adjustments', 'profit-commissions'] as $page) {
        $this->get('/client/'.$page)->assertRedirect('/login');
    }
});

test('users without the client role cannot access cedant pages', function () {
    $this->actingAs(User::factory()->create());
    foreach (['dashboard', 'policies', 'statements', 'claims', 'help', 'premium-adjustments', 'profit-commissions'] as $page) {
        $this->get('/client/'.$page)->assertForbidden();
    }
});

test('clients can open their portal and starter sections', function () {
    Role::create(['name' => 'client', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('client');
    $this->actingAs($user);
    $this->get('/client/dashboard')->assertOk()->assertSee('My Policies / Covers')->assertSee('Help &amp; Feedback', false);
    foreach (['policies', 'statements', 'claims', 'help', 'premium-adjustments', 'profit-commissions', 'profile'] as $page) {
        $this->get('/client/'.$page)->assertOk();
    }
});

test('authentication pages use cedant wording', function () {
    $this->get('/login')->assertOk()->assertSee('Welcome to your cedant portal');
    $this->get('/register')->assertOk()->assertSee('Create your cedant account');
});


test('broker tables support search pagination and detail views', function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('admin');
    $this->actingAs($user)->get('/admin/dashboard')->assertOk()->assertSee('Your overview')->assertSee('Recent submissions')->assertDontSee('Preview broker feedback');
    $this->get('/admin/profit-commissions')->assertOk()->assertSee('PCM-DEMO-001')->assertDontSee('CLM-DEMO-001');
    $this->get('/admin/submissions?q=no-such-reference')->assertOk()->assertSee('No submissions match');
    $this->get('/admin/submissions?page=2')->assertOk()->assertSee('PCM-DEMO-001');
    $this->get('/admin/submissions/CLM-DEMO-001')->assertOk()->assertSee('Claim form.pdf')->assertSee('Preview broker feedback');
    $this->get('/admin/help')->assertOk()->assertSee('HELP-DEMO-001');
    $this->get('/admin/submissions/HELP-DEMO-001')->assertOk()->assertSee('Statement clarification');
});
test('cedant preview does not show the other fictional company submissions', function () {
    Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('client');
    $this->actingAs($user)->get('/client/claims')->assertOk()->assertSee('CLM-DEMO-001')->assertDontSee('CLM-DEMO-003');
    $this->get('/admin/dashboard')->assertForbidden();
    $this->get('/client/submissions/CLM-DEMO-003')->assertNotFound();
    $this->get('/client/submissions/CLM-DEMO-001')->assertOk()->assertSee('Claim form.pdf');
    $this->get('/client/submissions/new')->assertOk()->assertSee('Preview submission');
});


test('each module opens its own submission form without a type selector', function () {
    Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('client');
    $this->actingAs($user);
    foreach ([
        'claims' => 'New claim',
        'premium-adjustments' => 'New premium adjustment',
        'profit-commissions' => 'New profit commission',
        'help' => 'New help request',
    ] as $path => $title) {
        $this->get('/client/'.$path)->assertOk()->assertSee('/client/'.$path.'/new', false);
        $this->get('/client/'.$path.'/new')->assertOk()->assertSee($title)
            ->assertDontSee('Submission type')->assertDontSee('<select', false);
    }
    $this->get('/client/claims/new')->assertSee('Loss details')->assertSee('Supporting documents (required)')->assertDontSee('Adjustment details');
    $this->get('/client/premium-adjustments/new')->assertSee('Adjustment details')->assertDontSee('Loss details');
});
