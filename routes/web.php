<?php

use App\Http\Controllers\DashboardRedirectController;
use App\Livewire\Admin\Access\Index as AccessManagement;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Users\Index as UserManagement;
use App\Livewire\Client\Dashboard as ClientDashboard;
use App\Livewire\Client\Profile\Edit as ClientProfile;
use App\Livewire\Frontend\Home;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardRedirectController::class)->name('dashboard');
});

Route::middleware(['auth', 'role_or_permission:super-admin|admin|view admin dashboard'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/dashboard', AdminDashboard::class)->name('dashboard');
        Route::get('/users', UserManagement::class)->middleware('can:manage users')->name('users.index');
        Route::get('/access', AccessManagement::class)->middleware('can:manage roles')->name('access.index');
    });

Route::middleware(['auth', 'role:client'])
    ->prefix('client')
    ->name('client.')
    ->group(function (): void {
        Route::get('/dashboard', ClientDashboard::class)->name('dashboard');
        Route::get('/profile', ClientProfile::class)->name('profile.edit');

        foreach ([
            'policies' => ['My Policies / Covers', 'Your company’s cover details and policy documents.', 'Records pending', 'Your policies will appear here', 'Policy records are not available in the portal yet.'],
            'statements' => ['Statement of Account', 'Your company’s statements and account information.', 'Records pending', 'Your statements will appear here', 'Statements and downloads are not available in the portal yet.'],
            'claims' => ['My Claims', 'A dedicated place for your company’s claims.', 'Coming soon', 'Claims submission is not open yet', 'Online claim submission and progress tracking will be available here. No claims can be submitted through this page yet.'],
            'help' => ['Help & Feedback', 'A place for your questions, support requests, and suggestions.', 'Coming soon', 'We’re getting your help centre ready', 'Online requests are not available yet. Please use your usual Afro-Asian contact for assistance.'],
        ] as $section => [$title, $description, $status, $emptyTitle, $emptyMessage]) {
            Route::view('/'.$section, 'client-section', compact('section', 'title', 'description', 'status', 'emptyTitle', 'emptyMessage'))->name($section);
        }
    });


// Static local preview: no authentication, sessions, or database records.
Route::get('/demo/{page?}', function (string $page = 'home') {
    abort_unless(app()->environment(['local', 'testing']), 404);
    $titles = ['home' => 'Home', 'policies' => 'My Policies / Covers', 'statements' => 'Statement of Account', 'claims' => 'My Claims', 'help' => 'Help & Feedback', 'account' => 'My account'];
    abort_unless(isset($titles[$page]), 404);
    return view('demo', ['page' => $page, 'title' => $titles[$page]]);
})->withoutMiddleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
])->name('cedant.demo');

