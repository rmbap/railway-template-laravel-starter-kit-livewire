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

        // garante tabela recommendations
        DB::statement("CREATE TABLE IF NOT EXISTS recommendations (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            company_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP NULL,
            from_channel VARCHAR(100),
            to_channel VARCHAR(100),
            pct_move INT,
            reason TEXT,
            expected_leads_gain DECIMAL(10,2),
            expected_revenue_gain DECIMAL(10,2),
            updated_at TIMESTAMP NULL
        )");

        $userId = auth()->id();
        $user = DB::table('users')->where('id', $userId)->first();
        $companyId = $user->company_id ?? null;

        if (!$companyId) {
            return redirect('/company/create');
        }

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

        $windowDays = 14;
        $end = now()->toDateString();
        $start = now()->subDays($windowDays - 1)->toDateString();

        $rows = DB::table('daily_metrics')
            ->selectRaw('channel, SUM(spend) as spend_sum, SUM(conversions) as conv_sum')
            ->where('company_id', $companyId)
            ->whereBetween('date', [$start, $end])
            ->groupBy('channel')
            ->orderBy('channel')
            ->get();

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

        $withCpa = array_filter($channels, fn($x) => $x['cpa'] !== null);
        usort($withCpa, fn($a, $b) => $a['cpa'] <=> $b['cpa']);
        $best = $withCpa[0] ?? null;
        $worst = $withCpa[count($withCpa) - 1] ?? null;

        $recommendationHtml = '';
        if (!$best || !$worst || $best['channel'] === $worst['channel']) {
            $recommendationHtml = '<div style="padding:14px;border:1px solid #ddd;border-radius:10px;">
                <b>Recomendação:</b> Dados insuficientes (cadastre pelo menos 2 canais com conversões).
            </div>';
        } else {
            $ratio = $best['cpa'] > 0 ? ($worst['cpa'] / $best['cpa']) : null;
            if ($ratio !== null && $ratio >= 1.3) {
                $recommendationHtml = '
                <div style="padding:14px;border:1px solid #ddd;border-radius:10px;">
                    <div style="font-size:18px;"><b>Recomendação</b></div>
                    <div style="margin-top:6px;">Mover verba de <b>'.$worst['channel'].'</b> → <b>'.$best['channel'].'</b></div>
                    <div style="margin-top:6px;color:#444;">Motivo: CPA de '.$worst['channel'].' é ~'.number_format(($ratio - 1) * 100, 0).' % maior que '.$best['channel'].'.</div>
                    <form method="POST" action="/recommendation/run" style="margin-top:10px;">
                        <input type="hidden" name="_token" value="'.csrf_token().'">
                        <input type="hidden" name="window_days" value="'.$windowDays.'">
                        <button type="submit" style="padding:10px 14px;border:1px solid #333;border-radius:10px;background:#111;color:#fff;cursor:pointer;">
                            Salvar recomendação
                        </button>
                    </form>
                </div>';
            } else {
                $recommendationHtml = '<div style="padding:14px;border:1px solid #ddd;border-radius:10px;">
                    <b>Recomendação:</b> Manter por enquanto (diferença de CPA pequena).
                    <div style="margin-top:6px;color:#444;">Dica: colete mais dias/volume para aumentar confiança.</div>
                </div>';
            }
        }

        $html = '
        <div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:980px; margin:24px auto; padding:0 14px;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                <div>
                    <div style="font-size:26px;font-weight:700;">Marketing Decision Engine</div>
                    <div style="color:#555;margin-top:4px;">Janela: últimos '.$windowDays.' dias ('.$start.' → '.$end.')</div>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <a href="/metrics/create" style="text-decoration:none;padding:10px 14px;border:1px solid #333;border-radius:10px;background:#111;color:#fff;">Registrar métricas</a>
                    <a href="/analysis" style="text-decoration:none;padding:10px 14px;border:1px solid #ddd;border-radius:10px;background:#fff;color:#111;">Ver análise</a>
                </div>
            </div>

            <div style="margin-top:18px; display:grid; grid-template-columns:1fr; gap:14px;">
        ';

        if (count($channels) === 0) {
            $html .= '
                <div style="padding:14px;border:1px solid #ddd;border-radius:10px;">
                    <b>Nenhuma métrica encontrada.</b>
                    <div style="margin-top:6px;">Clique em "Registrar métricas" para começar.</div>
                </div>
            ';
        } else {
            $html .= '
                <div style="padding:14px;border:1px solid #ddd;border-radius:10px;">
                    <div style="font-size:18px;font-weight:700;margin-bottom:10px;">Resumo por canal</div>
                    <table style="width:100%;border-collapse:collapse;">
                        <tr style="text-align:left;border-bottom:1px solid #eee;">
                            <th style="padding:10px 8px;">Canal</th>
                            <th style="padding:10px 8px;">Spend</th>
                            <th style="padding:10px 8px;">Conversions</th>
                            <th style="padding:10px 8px;">CPA</th>
                        </tr>
            ';

            foreach ($channels as $c) {
                $html .= '
                    <tr style="border-bottom:1px solid #f2f2f2;">
                        <td style="padding:10px 8px;">'.htmlspecialchars($c['channel']).'</td>
                        <td style="padding:10px 8px;">'.number_format($c['spend'], 2, ',', '.').'</td>
                        <td style="padding:10px 8px;">'.$c['conv'].'</td>
                        <td style="padding:10px 8px;">'.($c['cpa'] === null ? '—' : number_format($c['cpa'], 2, ',', '.')).'</td>
                    </tr>
                ';
            }

            $html .= '
                    </table>
                </div>
            ';

            $html .= $recommendationHtml;
        }

        $html .= '
            </div>

            <div style="margin-top:18px;color:#666;">
                <a href="/settings" style="color:#666;">Configurações</a>
            </div>
        </div>
        ';

        return $html;
    })->name('dashboard');

    /**
     * FORM: CRIAR EMPRESA
     */
    Route::get('/company/create', function () {
        return '
            <div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:720px; margin:24px auto; padding:0 14px;">
                <h1>Criar empresa</h1>

                <form method="POST" action="/company/create" style="margin-top:14px;">
                    <input type="hidden" name="_token" value="' . csrf_token() . '">

                    <label>Nome da empresa</label><br>
                    <input name="name" required style="padding:10px; width:100%; max-width:420px; border:1px solid #ddd; border-radius:10px;"><br><br>

                    <button type="submit" style="padding:10px 14px;border:1px solid #333;border-radius:10px;background:#111;color:#fff;cursor:pointer;">
                        Salvar
                    </button>
                </form>
            </div>
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
            <div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:720px; margin:24px auto; padding:0 14px;">
                <h1>Registrar métricas</h1>

                <form method="POST" action="/metrics/create" style="margin-top:14px;">
                    <input type="hidden" name="_token" value="' . csrf_token() . '">

                    <label>Data</label><br>
                    <input type="date" name="date" required style="padding:10px;border:1px solid #ddd;border-radius:10px;"><br><br>

                    <label>Canal</label><br>
                    <input name="channel" placeholder="Google / Meta / TikTok" required style="padding:10px; width:100%; max-width:420px; border:1px solid #ddd; border-radius:10px;"><br><br>

                    <label>Spend</label><br>
                    <input name="spend" type="number" step="0.01" required style="padding:10px; width:200px; border:1px solid #ddd; border-radius:10px;"><br><br>

                    <label>Conversions</label><br>
                    <input name="conversions" type="number" required style="padding:10px; width:200px; border:1px solid #ddd; border-radius:10px;"><br><br>

                    <label>Revenue (opcional)</label><br>
                    <input name="revenue" type="number" step="0.01" style="padding:10px; width:200px; border:1px solid #ddd; border-radius:10px;"><br><br>

                    <button type="submit" style="padding:10px 14px;border:1px solid #333;border-radius:10px;background:#111;color:#fff;cursor:pointer;">
                        Salvar
                    </button>
                </form>

                <p style="margin-top:16px;">
                    <a href="/dashboard">Voltar ao dashboard</a> |
                    <a href="/analysis">Ver análise</a>
                </p>
            </div>
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
     * ANÁLISE
     */
    Route::get('/analysis', function () {

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
            ->selectRaw('channel, SUM(spend) as spend_sum, SUM(conversions) as conv_sum')
            ->where('company_id', $companyId)
            ->whereBetween('date', [$start, $end])
            ->groupBy('channel')
            ->orderBy('channel')
            ->get();

        if ($rows->count() === 0) {
            return '
                <div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:720px; margin:24px auto; padding:0 14px;">
                    <h1>Análise (últimos '.$windowDays.' dias)</h1>
                    <p>Nenhuma métrica encontrada ainda.</p>
                    <p><a href="/metrics/create">Registrar métricas</a> | <a href="/dashboard">Voltar</a></p>
                </div>
            ';
        }

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

        $withCpa = array_filter($channels, fn($x) => $x['cpa'] !== null);
        usort($withCpa, fn($a, $b) => $a['cpa'] <=> $b['cpa']);
        $best = $withCpa[0] ?? null;
        $worst = $withCpa[count($withCpa) - 1] ?? null;

        $recommendationHtml = '';
        if (!$best || !$worst || $best['channel'] === $worst['channel']) {
            $recommendationHtml = '<p><b>Recomendação:</b> dados insuficientes (precisa de pelo menos 2 canais com conversões).</p>';
        } else {
            $ratio = $best['cpa'] > 0 ? ($worst['cpa'] / $best['cpa']) : null;
            if ($ratio !== null && $ratio >= 1.3) {
                $recommendationHtml =
                    '<p><b>Recomendação:</b> reduzir verba de <b>'.$worst['channel'].'</b> e aumentar <b>'.$best['channel'].'</b>.</p>
                     <p>Motivo: CPA de '.$worst['channel'].' é ~'.number_format(($ratio - 1) * 100, 0).' % maior que '.$best['channel'].'.</p>
                     <p>
                        <a href="/simulate?from='.urlencode($worst['channel']).'&to='.urlencode($best['channel']).'&pct=10">Simular 10%</a> |
                        <a href="/simulate?from='.urlencode($worst['channel']).'&to='.urlencode($best['channel']).'&pct=20">Simular 20%</a> |
                        <a href="/simulate?from='.urlencode($worst['channel']).'&to='.urlencode($best['channel']).'&pct=30">Simular 30%</a>
                     </p>
                     <form method="POST" action="/recommendation/run" style="margin-top:10px;">
                        <input type="hidden" name="_token" value="'.csrf_token().'">
                        <input type="hidden" name="window_days" value="'.$windowDays.'">
                        <button type="submit" style="padding:10px 14px;border:1px solid #333;border-radius:10px;background:#111;color:#fff;cursor:pointer;">
                            Salvar recomendação
                        </button>
                     </form>';
            } else {
                $recommendationHtml =
                    '<p><b>Recomendação:</b> manter por enquanto (diferença de CPA pequena).</p>
                     <p>Dica: colete mais dias ou aumente volume para ter confiança.</p>';
            }
        }

        $html = '<div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:980px; margin:24px auto; padding:0 14px;">';
        $html .= '<h1>Análise (últimos '.$windowDays.' dias)</h1>';
        $html .= '<p>Janela: '.$start.' até '.$end.'</p>';

        $html .= '<table border="1" cellpadding="8" cellspacing="0" style="width:100%;border-collapse:collapse;">
                    <tr>
                        <th style="text-align:left;">Canal</th>
                        <th style="text-align:left;">Spend</th>
                        <th style="text-align:left;">Conversions</th>
                        <th style="text-align:left;">CPA</th>
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
        $html .= '<p style="margin-top:16px;"><a href="/metrics/create">Registrar mais métricas</a> | <a href="/dashboard">Voltar</a></p>';
        $html .= '</div>';

        return $html;
    })->name('analysis');

    /**
     * SIMULAÇÃO
     */
    Route::get('/simulate', function (Request $request) {

        $from = $request->get('from');
        $to = $request->get('to');
        $pct = (float) $request->get('pct', 20);

        $userId = auth()->id();
        $user = DB::table('users')->where('id', $userId)->first();
        $companyId = $user->company_id ?? null;

        $rows = DB::table('daily_metrics')
            ->selectRaw('channel, SUM(spend) as spend_sum, SUM(conversions) as conv_sum')
            ->where('company_id', $companyId)
            ->groupBy('channel')
            ->get();

        $data = [];

        foreach ($rows as $r) {
            $spend = (float) $r->spend_sum;
            $conv = (int) $r->conv_sum;

            $data[$r->channel] = [
                'spend' => $spend,
                'conv' => $conv,
                'cpa' => $conv > 0 ? $spend / $conv : null
            ];
        }

        if (!isset($data[$from]) || !isset($data[$to])) {
            return "Canais inválidos";
        }

        $move = $data[$from]['spend'] * ($pct / 100);

        $fromNew = $data[$from]['spend'] - $move;
        $toNew = $data[$to]['spend'] + $move;

        $convBefore =
            $data[$from]['spend'] / $data[$from]['cpa'] +
            $data[$to]['spend'] / $data[$to]['cpa'];

        $convAfter =
            $fromNew / $data[$from]['cpa'] +
            $toNew / $data[$to]['cpa'];

        $delta = $convAfter - $convBefore;

        return "
        <div style='font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:720px; margin:24px auto; padding:0 14px;'>
            <h1>Simulação</h1>

            <p>Mover $pct% de <b>$from</b> para <b>$to</b></p>

            <h2>Antes</h2>

            Spend $from: ".number_format($data[$from]['spend'], 2)."<br>
            Spend $to: ".number_format($data[$to]['spend'], 2)."<br>

            <h2>Depois</h2>

            Spend $from: ".number_format($fromNew, 2)."<br>
            Spend $to: ".number_format($toNew, 2)."<br>

            <h2>Impacto estimado</h2>

            Conversões antes: ".number_format($convBefore, 2)."<br>
            Conversões depois: ".number_format($convAfter, 2)."<br><br>

            <b>Δ Conversões: ".number_format($delta, 2)."</b>

            <p><a href='/analysis'>Voltar</a></p>
        </div>
        ";
    })->name('simulate');

    /**
     * SALVAR RECOMENDAÇÃO
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
            $spend = (float) $r->spend_sum;
            $conv = (int) $r->conv_sum;
            $cpa = $conv > 0 ? ($spend / $conv) : null;
            $channels[] = [
                'channel' => $r->channel,
                'spend' => $spend,
                'conversions' => $conv,
                'cpa' => $cpa
            ];
        }

        $withCpa = array_filter($channels, fn($x) => $x['cpa'] !== null);
        usort($withCpa, fn($a, $b) => $a['cpa'] <=> $b['cpa']);
        $best = $withCpa[0] ?? null;
        $worst = $withCpa[count($withCpa) - 1] ?? null;

        $decision = 'Sem decisão (dados insuficientes)';
        if ($best && $worst && $best['channel'] !== $worst['channel'] && $best['cpa'] > 0) {
            $ratio = $worst['cpa'] / $best['cpa'];
            if ($ratio >= 1.3) {
                $decision = 'Reduzir '.$worst['channel'].' e aumentar '.$best['channel'].' (CPA pior ~'.round(($ratio - 1) * 100).'%)';
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
        ->name('two-factor.show');
});
