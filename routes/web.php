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

        // Garante que a coluna company_id exista no users
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

        return view('dashboard');
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
     * Cria tabela companies se não existir e liga usuário à empresa.
     */
    Route::post('/company/create', function (Request $request) {
        $request->validate(['name' => 'required|min:2|max:120']);

        // Cria tabela companies se não existir
        DB::statement("CREATE TABLE IF NOT EXISTS companies (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            owner_user_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )");

        // Cria coluna company_id em users se não existir
        $columns = DB::select("SHOW COLUMNS FROM users LIKE 'company_id'");
        if (count($columns) === 0) {
            DB::statement("ALTER TABLE users ADD company_id BIGINT UNSIGNED NULL");
        }

        $userId = auth()->id();

        // Cria empresa
        DB::table('companies')->insert([
            'name' => $request->name,
            'owner_user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Pega o ID recém-criado
        $companyId = DB::getPdo()->lastInsertId();

        // Liga usuário à empresa
        DB::table('users')->where('id', $userId)->update([
            'company_id' => $companyId
        ]);

        return redirect('/dashboard');
    })->name('company.store');

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
