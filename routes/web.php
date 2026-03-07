<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\MetricsController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\PlannerController;
use App\Http\Controllers\RecommendationController;

Route::get('/up', function () {
    return response('OK', 200);
});

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    /**
     * DASHBOARD
     */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /**
     * FORM: CRIAR EMPRESA
     */
    Route::get('/company/create', [CompanyController::class, 'create'])->name('company.create');

    /**
     * POST: SALVAR EMPRESA
     */
    Route::post('/company/create', [CompanyController::class, 'store'])->name('company.store');
    /**
     * FORM: REGISTRAR MÉTRICAS
     */
   Route::get('/metrics/create', [MetricsController::class, 'create'])->name('metrics.create');
    /**
     * POST: SALVAR MÉTRICAS
     */
   Route::post('/metrics/create', [MetricsController::class, 'store'])->name('metrics.store');
    /**
     * ANÁLISE
     */
   Route::get('/analysis', [AnalysisController::class, 'index'])->name('analysis');
    /**
     * SIMULAÇÃO
     */
   Route::get('/simulate', [SimulationController::class, 'run'])->name('simulate');
    /**
     * PLANNER - FORM
     */
   Route::get('/planner', [PlannerController::class, 'form'])->name('planner.form');

    /**
     * PLANNER - RESULTADO
     */
    Route::post('/planner', [PlannerController::class, 'run'])->name('planner.run');

    /**
     * SALVAR RECOMENDAÇÃO
     */
   Route::post('/recommendation/run', [RecommendationController::class, 'store'])->name('recommendation.run');

    /**
     * SETTINGS
     */
    Route::redirect('settings', 'settings/profile');
    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');
    Route::get('settings/two-factor', TwoFactor::class)->name('two-factor.show');
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/company/{id}', [AdminController::class, 'show'])->name('admin.company.show');

});
