<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    /**
     * DASHBOARD
     * Se o usuário não tiver empresa, manda criar.
     * Também garante que a coluna users.company_id exista (evita erro 500).
     */
    Route::get('/dashboard', function () {

        // garante coluna company_id (evita 500 se ainda não existir)
        $columns = DB::select("SHOW COLUMNS FROM users LIKE 'company_id'");
        if (count($columns) === 0) {
            DB::statement("ALTER TABLE users ADD company_id BIGINT UNSIGNED NULL");
        }

        $userId = auth()->id();
        $user = DB::table('users')->where('id', $userId)->first();

        $companyId = $user->company_id ?? null;

        if (!$user || !$companyId) {
            return redirect('/company/create');
        }

        // atalho: mostra links úteis no dashboard (pra você testar rápido)
        return '
            <h1>Dashboard</h1>
            <p>Empresa ID: '.$companyId.'</p>
            <p><a href="/metrics/create">Registrar métricas</a></p>
            <p><a href="/settings">Configurações</a></p>
        ';
    })->name('dashboard');

    /**
     * FORM: CRIAR EMPRESA
     */
    Route::get('/company/create', function () {
        return '
            <h1>Criar empresa</h1>
            <form method="POST" action="/company/create">
                <input type="hidden" name="_token" value="' . csrf_token() . '">

                <label>Nome da empresa</label><br>
                <input name="name" required style="padding:8px; width:320px;"><br><br>

                <button type="submit" style="padding:10px 14px;">Salvar</button>
            </form>
        ';
    })->name('company.create');

    /**
     * POST: SALVAR EMPRESA
     */
    Route::post('/company/create', function (Request $request) {
        $request->validate(['name' => 'required|min:2|max:120']);

        // cria tabela companies se não existir
        DB::statement("CREATE TABLE IF NOT EXISTS companies (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            owner_user_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )");

        // cria coluna company_id em users se não existir
        $columns = DB::select("SHOW COLUMNS FROM users LIKE 'company_id'");
        if (count($columns) === 0) {
            DB::statement("ALTER TABLE users ADD company_id BIGINT UNSIGNED NULL");
        }

        $userId = auth()->id();

        // cria empresa
        DB::table('companies')->insert([
            'name' => $request->name,
            'owner_user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $companyId = DB::getPdo()->lastInsertId();

        // liga usuário à empresa
        DB::table('users')->where('id', $userId)->update([
            'company_id' => $companyId
        ]);

        return redirect('/dashboard');
    })->name('company.store');

    /**
     * FORM: REGISTRAR MÉTRICAS
     */
    Route::get('/metrics/create', function () {
        return '
            <h1>Registrar métricas</h1>

            <form method="POST" action="/metrics/create">
                <input type="hidden" name="_token" value="' . csrf_token() . '">

                <label>Data</label><br>
                <input type="date" name="date" required><br><br>

                <label>Canal</label><br>
                <input name="channel" placeholder="Google / Meta / TikTok" required style="padding:8px; width:320px;"><br><br>

                <label>Spend</label><br>
                <input name="spend" type="number" step="0.01" required style="padding:8px; width:200px;"><br><br>

                <label>Conversions</label><br>
                <input name="conversions" type="number" required style="padding:8px; width:200px;"><br><br>

                <label>Revenue (opcional)</label><br>
                <input name="revenue" type="number" step="0.01" style="padding:8px; width:200px;"><br><br>

                <button type="submit" style="padding:10px 14px;">Salvar</button>
            </form>

            <p style="margin-top:16px;"><a href="/dashboard">Voltar ao dashboard</a></p>
        ';
    })->name('metrics.create');

    /**
     * POST: SALVAR MÉTRICAS
     */
    Route::post('/metrics/create', function (Request $request) {

        $request->validate([
            'date' => 'required',
            'channel' => 'required|min:1|max:100',
            'spend' => 'required|numeric|min:0',
            'conversions' => 'required|integer|min:0',
            'revenue' => 'nullable|numeric|min:0',
        ]);

        // garante tabela daily_metrics
        DB::statement("CREATE TABLE IF NOT EXISTS daily_metrics (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            company_id BIGINT UNSIGNED NOT NULL,
            date DATE NOT NULL,
            channel VARCHAR(100) NOT NULL,
            spend DECIMAL(10,2) NOT NULL,
            conversions INT NOT NULL,
            revenue DECIMAL(10,2) NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )");

        $userId = auth()->id();
        $user = DB::table('users')->where('id', $userId)->first();
        $companyId = $user->company_id ?? null;

        if (!$companyId) {
            return redirect('/company/create');
        }

        DB::table('daily_metrics')->insert([
            'company_id' => $companyId,
            'date' => $request->date,
            'channel' => $request->channel,
            'spend' => $request->spend,
            'conversions' => $request->conversions,
            'revenue' => $request->revenue,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('/dashboard');
    })->name('metrics.store');

    /**
     * SETTINGS (Starter Kit)
     */
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            )
        )
        ->name('two-factor.show');
});
