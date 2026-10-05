<?php

use App\Http\Controllers\CedantRecordsController;
use App\Http\Controllers\CedantSubmissionController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\SubmissionReviewController;
use App\Livewire\Admin\Access\Index as AccessManagement;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Users\Index as UserManagement;
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
            Route::get('/submissions/new', [CedantSubmissionController::class, 'create'])->defaults('module', 'claims')->name('submissions.create');
            Route::get('/claims/new', [CedantSubmissionController::class, 'create'])->defaults('module', 'claims')->name('claims.create');
            Route::get('/premium-adjustments/new', [CedantSubmissionController::class, 'create'])->defaults('module', 'adjustments')->name('adjustments.create');
            Route::post('/claims', [CedantSubmissionController::class, 'store'])->defaults('module', 'claims')->middleware('throttle:30,1')->name('claims.store');
            Route::post('/premium-adjustments', [CedantSubmissionController::class, 'store'])->defaults('module', 'adjustments')->middleware('throttle:30,1')->name('adjustments.store');
            Route::get('/cover-options', [CedantSubmissionController::class, 'coverOptions'])->name('cover-options');
        }
        Route::get('/policies', [CedantRecordsController::class, 'covers'])->name('policies');
        Route::get('/policies/{number}', [CedantRecordsController::class, 'cover'])->name('policies.show');
        Route::get('/statements', [CedantRecordsController::class, 'statements'])->name('statements');
        Route::get('/rbs-adjustments/{id}', [CedantRecordsController::class, 'officialAdjustment'])->whereNumber('id')->name('rbs-adjustments.show');
        Route::get('/rbs-claims/{number}', [CedantRecordsController::class, 'officialClaim'])->name('rbs-claims.show');
        Route::get('/documents/{kind}/{id}', [CedantRecordsController::class, 'document'])->whereNumber('id')->name('documents.download');
        Route::get('/submissions', [CedantRecordsController::class, 'index'])->defaults('category', 'all')->name('submissions');
        Route::post('/submissions/{id}/feedback', [SubmissionReviewController::class, 'feedback'])->middleware('throttle:30,1')->name('submissions.feedback');
        Route::post('/submissions/{id}/documents', [SubmissionReviewController::class, 'documents'])->middleware('throttle:15,1')->name('submissions.documents');
        if ($prefix === 'admin') {
            // Portal status review deferred; preserve handler for a future phase.
            // Route::post('/submissions/{id}/review', [SubmissionReviewController::class, 'review'])->middleware(['role:admin|super-admin', 'throttle:30,1'])->name('submissions.review');
        }
        Route::get('/submissions/{id}', [CedantRecordsController::class, 'show'])->name('submissions.show');
        foreach (['claims' => 'claims', 'premium-adjustments' => 'adjustments', 'help' => 'help'] as $path => $category) {
            Route::get('/'.$path, [CedantRecordsController::class, 'index'])->defaults('category', $category)->name($category);
        }
    });
}
