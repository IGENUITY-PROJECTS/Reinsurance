<?php
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\SubmissionPreviewController;
use App\Livewire\Admin\Access\Index as AccessManagement;
use App\Livewire\Admin\Users\Index as UserManagement;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Client\Dashboard as ClientDashboard;
use App\Livewire\Client\Profile\Edit as ClientProfile;
use App\Livewire\Frontend\Home;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');
Route::get('/dashboard', DashboardRedirectController::class)->middleware('auth')->name('dashboard');

foreach (['admin' => ['auth', 'role_or_permission:super-admin|admin|view admin dashboard'], 'client' => ['auth', 'role:client']] as $prefix => $middleware) {
    Route::middleware($middleware)->prefix($prefix)->name($prefix.'.')->group(function () use ($prefix) {
        Route::get('/dashboard', $prefix === 'admin' ? AdminDashboard::class : ClientDashboard::class)->name('dashboard');
        if ($prefix === 'admin') {
            Route::get('/users', UserManagement::class)->middleware('can:manage users')->name('users.index');
            Route::get('/access', AccessManagement::class)->middleware('can:manage roles')->name('access.index');
        } else {
            Route::get('/profile', ClientProfile::class)->name('profile.edit');
            Route::view('/submissions/new', 'new-submission', ['module' => 'claims', 'formTitle' => 'New claim', 'backLabel' => 'Claims'])->name('submissions.create');
            Route::view('/claims/new', 'new-submission', ['module' => 'claims', 'formTitle' => 'New claim', 'backLabel' => 'Claims'])->name('claims.create');
            Route::view('/premium-adjustments/new', 'new-submission', ['module' => 'adjustments', 'formTitle' => 'New premium adjustment', 'backLabel' => 'Premium Adjustments'])->name('adjustments.create');
            Route::view('/profit-commissions/new', 'new-submission', ['module' => 'commissions', 'formTitle' => 'New profit commission', 'backLabel' => 'Profit Commissions'])->name('commissions.create');
            Route::view('/help/new', 'new-submission', ['module' => 'help', 'formTitle' => 'New help request', 'backLabel' => 'Help & Feedback'])->name('help.create');
            foreach (['policies' => 'My Policies / Covers', 'statements' => 'Statement of Account'] as $path => $title) {
                Route::view('/'.$path, 'client-section', ['title' => $title, 'description' => 'Your company records.', 'section' => $path, 'status' => 'Records pending', 'emptyTitle' => 'Your records will appear here', 'emptyMessage' => 'Records are not available yet.'])->name($path);
            }
        }
        Route::get('/submissions', [SubmissionPreviewController::class, 'index'])->defaults('category', 'all')->name('submissions');
        Route::get('/submissions/{id}', [SubmissionPreviewController::class, 'show'])->name('submissions.show');
        foreach (['claims' => 'claims', 'premium-adjustments' => 'adjustments', 'profit-commissions' => 'commissions', 'help' => 'help'] as $path => $category) {
            Route::get('/'.$path, [SubmissionPreviewController::class, 'index'])->defaults('category', $category)->name($category);
        }
    });
}

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




