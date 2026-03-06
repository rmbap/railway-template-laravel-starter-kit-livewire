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
     */
    Route::get('/dashboard', function () {

        // garante coluna company_id
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

        return '
            <h1>Dashboard</h1>
            <p>Empresa ID: '.$companyId.'</p>

            <ul>
                <li><a href="/metrics/create">Registrar métricas</a></li>
                <li><a href="/analysis">Análise (últimos 14 dias)</a></li>
                <li><a href="/settings">Configurações</a></li>
            </ul>
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

        DB::statement("CREATE TABLE IF NOT EXISTS companies (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            owner_user_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )");

        $columns = DB::select("SHOW COLUMNS FROM users LIKE 'company_id'");
        if (count($columns) === 0) {
            DB::statement("ALTER TABLE users ADD company_id BIGINT UNSIGNED NULL");
        }

        $userId = auth()->id();

        DB::table('companies')->insert([
            'name' => $request->name,
            'owner_user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $companyId = DB::getPdo()->lastInsertId();

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

            <p style="margin-top:16px;">
                <a href="/dashboard">Voltar ao dashboard</a> |
                <a href="/analysis">Ver análise</a>
            </p>
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

        return redirect('/analysis');
    })->name('metrics.store');

    /**
     * ANÁLISE (últimos 14 dias): CPA por canal + recomendação simples
     */
    Route::get('/analysis', function () {

        // garante tabela daily_metrics (se ainda não existir, não quebra)
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

        $windowDays = 14;
        $end = now()->toDateString();
        $start = now()->subDays($windowDays - 1)->toDateString();

        $rows = DB::table('daily_metrics')
            ->selectRaw('channel, SUM(spend) as spend_sum, SUM(conversions) as conv_sum, SUM(COALESCE(revenue,0)) as rev_sum')
            ->where('company_id', $companyId)
            ->whereBetween('date', [$start, $end])
            ->groupBy('channel')
            ->orderBy('channel')
            ->get();

        if ($rows->count() === 0) {
            return '
                <h1>Análise (últimos '.$windowDays.' dias)</h1>
                <p>Nenhuma métrica encontrada ainda.</p>
                <p><a href="/metrics/create">Registrar métricas</a> | <a href="/dashboard">Voltar</a></p>
            ';
        }

        // Calcula CPA por canal (spend / conversions)
        $channels = [];
        foreach ($rows as $r) {
            $spend = (float) $r->spend_sum;
            $conv  = (int) $r->conv_sum;
            $cpa   = $conv > 0 ? ($spend / $conv) : null;

            $channels[] = [
                'channel' => $r->channel,
                'spend' => $spend,
                'conv' => $conv,
                'cpa' => $cpa,
            ];
        }

        // Ordena por melhor CPA (menor)
        $withCpa = array_filter($channels, fn($x) => $x['cpa'] !== null);
        usort($withCpa, fn($a,$b) => $a['cpa'] <=> $b['cpa']);

        $best = $withCpa[0] ?? null;
        $worst = $withCpa[count($withCpa)-1] ?? null;

        $recommendationHtml = '';
        if (!$best || !$worst || $best['channel'] === $worst['channel']) {
            $recommendationHtml = '<p><b>Recomendação:</b> dados insuficientes (precisa de pelo menos 2 canais com conversões).</p>';
        } else {
            $bestCpa = $best['cpa'];
            $worstCpa = $worst['cpa'];

            // regra simples: se pior é 30%+ mais caro que melhor, recomenda mover verba
            $ratio = $bestCpa > 0 ? ($worstCpa / $bestCpa) : null;

            if ($ratio !== null && $ratio >= 1.3) {
                $recommendationHtml =
                    '<p><b>Recomendação:</b> reduzir verba de <b>'.$worst['channel'].'</b> e aumentar <b>'.$best['channel'].'</b>.</p>
                     <p>Motivo: CPA de '.$worst['channel'].' é ~'.number_format(($ratio-1)*100, 0).' % maior que '.$best['channel'].'.</p>
                     <form method="POST" action="/recommendation/run">
                        <input type="hidden" name="_token" value="'.csrf_token().'">
                        <input type="hidden" name="window_days" value="'.$windowDays.'">
                        <button type="submit" style="padding:10px 14px;">Salvar recomendação</button>
                     </form>';
            } else {
                $recommendationHtml =
                    '<p><b>Recomendação:</b> manter por enquanto (diferença de CPA pequena).</p>
                     <p>Dica: colete mais dias ou aumente volume para ter confiança.</p>';
            }
        }

        $html = '<h1>Análise (últimos '.$windowDays.' dias)</h1>';
        $html .= '<p>Janela: '.$start.' até '.$end.'</p>';

        $html .= '<table border="1" cellpadding="8" cellspacing="0">
                    <tr>
                        <th>Canal</th>
                        <th>Spend</th>
                        <th>Conversions</th>
                        <th>CPA</th>
                    </tr>';

        foreach ($channels as $c) {
            $html .= '<tr>
                        <td>'.htmlspecialchars($c['channel']).'</td>
                        <td>'.number_format($c['spend'], 2, ',', '.').'</td>
                        <td>'.$c['conv'].'</td>
                        <td>'.($c['cpa'] === null ? '—' : number_format($c['cpa'], 2, ',', '.')).'</td>
                      </tr>';
        }

        $html .= '</table>';

        $html .= '<div style="margin-top:16px;">'.$recommendationHtml.'</div>';

        $html .= '<p style="margin-top:16px;">
                    <a href="/metrics/create">Registrar mais métricas</a> |
                    <a href="/dashboard">Voltar ao dashboard</a>
                  </p>';

        return $html;
    })->name('analysis');

    /**
     * SALVAR RECOMENDAÇÃO (opcional)
     */
    Route::post('/recommendation/run', function (Request $request) {

        $userId = auth()->id();
        $user = DB::table('users')->where('id', $userId)->first();
        $companyId = $user->company_id ?? null;

        if (!$companyId) {
            return redirect('/company/create');
        }

        DB::statement("CREATE TABLE IF NOT EXISTS recommendations (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            company_id BIGINT UNSIGNED NOT NULL,
            window_days INT NOT NULL,
            snapshot_json LONGTEXT NULL,
            decision_text VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )");

        // monta snapshot simples (últimos 14 dias agregados)
        $windowDays = (int) ($request->window_days ?? 14);
        $end = now()->toDateString();
        $start = now()->subDays($windowDays - 1)->toDateString();

        $rows = DB::table('daily_metrics')
            ->selectRaw('channel, SUM(spend) as spend_sum, SUM(conversions) as conv_sum')
            ->where('company_id', $companyId)
            ->whereBetween('date', [$start, $end])
            ->groupBy('channel')
            ->get();

        $channels = [];
        foreach ($rows as $r) {
            $spend = (float)$r->spend_sum;
            $conv = (int)$r->conv_sum;
            $cpa = $conv > 0 ? ($spend / $conv) : null;
            $channels[] = ['channel'=>$r->channel,'spend'=>$spend,'conversions'=>$conv,'cpa'=>$cpa];
        }

        $withCpa = array_filter($channels, fn($x) => $x['cpa'] !== null);
        usort($withCpa, fn($a,$b) => $a['cpa'] <=> $b['cpa']);
        $best = $withCpa[0] ?? null;
        $worst = $withCpa[count($withCpa)-1] ?? null;

        $decision = 'Sem decisão (dados insuficientes)';
        if ($best && $worst && $best['channel'] !== $worst['channel'] && $best['cpa'] > 0) {
            $ratio = $worst['cpa'] / $best['cpa'];
            if ($ratio >= 1.3) {
                $decision = 'Reduzir '.$worst['channel'].' e aumentar '.$best['channel'].' (CPA pior ~'.round(($ratio-1)*100).'%)';
            } else {
                $decision = 'Manter (diferença pequena)';
            }
        }

        $snapshot = [
            'window_days' => $windowDays,
            'window_start' => $start,
            'window_end' => $end,
            'channels' => $channels,
            'decision' => $decision,
        ];

        DB::table('recommendations')->insert([
            'company_id' => $companyId,
            'window_days' => $windowDays,
            'snapshot_json' => json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'decision_text' => $decision,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('/analysis');
    })->name('recommendation.run');

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
