<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

/**
 * Topbar simples (MVP)
 */
function topbar_html(string $active = ''): string
{
    $link = function (string $href, string $label, string $key) use ($active) {
        $isActive = $active === $key;
        $base = 'display:inline-block;padding:8px 12px;border-radius:10px;text-decoration:none;';
        $style = $isActive
            ? $base . 'background:#111;color:#fff;'
            : $base . 'background:#fff;color:#111;border:1px solid #e5e5e5;';
        return '<a href="' . $href . '" style="' . $style . '">' . $label . '</a>';
    };

    // logout do starter kit costuma ser POST em /logout
    $logoutForm = '
        <form method="POST" action="/logout" style="display:inline;">
            <input type="hidden" name="_token" value="' . csrf_token() . '">
            <button type="submit" style="padding:8px 12px;border-radius:10px;border:1px solid #e5e5e5;background:#fff;cursor:pointer;">
                Sair
            </button>
        </form>
    ';

    return '
    <div style="position:sticky;top:0;z-index:50;background:#fff;border-bottom:1px solid #eee;">
      <div style="max-width:1100px;margin:0 auto;padding:12px 14px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
          <div style="font-weight:900;">Budget Engine</div>
          ' . $link('/dashboard', 'Dashboard', 'dashboard') . '
          ' . $link('/metrics/create', 'Métricas', 'metrics') . '
          ' . $link('/analysis', 'Análise', 'analysis') . '
          ' . $link('/simulate', 'Simulação', 'simulate') . '
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
          ' . $link('/settings', 'Settings', 'settings') . '
          ' . $logoutForm . '
        </div>
      </div>
    </div>
    ';
}

/**
 * Helpers de banco (MVP)
 */
function ensure_company_id_column(): void
{
    $columns = DB::select("SHOW COLUMNS FROM users LIKE 'company_id'");
    if (count($columns) === 0) {
        DB::statement("ALTER TABLE users ADD company_id BIGINT UNSIGNED NULL");
    }
}

function ensure_companies_table(): void
{
    DB::statement("CREATE TABLE IF NOT EXISTS companies (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        owner_user_id BIGINT UNSIGNED NOT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    )");
}

function ensure_daily_metrics_table(): void
{
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
}

/**
 * Agrega métricas por canal e calcula CPA
 */
function get_channel_summary(int $companyId, int $windowDays = 14): array
{
    ensure_daily_metrics_table();

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
        $conv = (int) $r->conv_sum;
        $cpa = $conv > 0 ? ($spend / $conv) : null;

        $channels[] = [
            'channel' => $r->channel,
            'spend' => $spend,
            'conv' => $conv,
            'cpa' => $cpa,
        ];
    }

    // melhor/pior CPA
    $withCpa = array_filter($channels, fn($x) => $x['cpa'] !== null);
    usort($withCpa, fn($a, $b) => $a['cpa'] <=> $b['cpa']);
    $best = $withCpa[0] ?? null;
    $worst = $withCpa[count($withCpa) - 1] ?? null;

    return [
        'windowDays' => $windowDays,
        'start' => $start,
        'end' => $end,
        'channels' => $channels,
        'best' => $best,
        'worst' => $worst,
    ];
}

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    /**
     * DASHBOARD
     */
    Route::get('/dashboard', function () {

        ensure_company_id_column();

        $userId = auth()->id();
        $user = DB::table('users')->where('id', $userId)->first();
        $companyId = $user->company_id ?? null;

        if (!$companyId) {
            return redirect('/company/create');
        }

        $summary = get_channel_summary($companyId, 14);
        $channels = $summary['channels'];
        $best = $summary['best'];
        $worst = $summary['worst'];

        $html = topbar_html('dashboard') . '
        <div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:1100px; margin:18px auto; padding:0 14px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap;">
                <div>
                    <div style="font-size:26px;font-weight:900;">Dashboard</div>
                    <div style="color:#666;margin-top:4px;">Janela: últimos ' . $summary['windowDays'] . ' dias (' . $summary['start'] . ' → ' . $summary['end'] . ')</div>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <a href="/metrics/create" style="text-decoration:none;padding:10px 14px;border:1px solid #333;border-radius:12px;background:#111;color:#fff;">Registrar métricas</a>
                    <a href="/analysis" style="text-decoration:none;padding:10px 14px;border:1px solid #e5e5e5;border-radius:12px;background:#fff;color:#111;">Ver análise</a>
                </div>
            </div>

            <div style="margin-top:14px;display:grid;grid-template-columns:1fr;gap:12px;">
        ';

        if (count($channels) === 0) {
            $html .= '
                <div style="padding:14px;border:1px solid #ddd;border-radius:12px;">
                    <b>Nenhuma métrica cadastrada ainda.</b>
                    <div style="margin-top:6px;color:#555;">Vá em "Métricas" e cadastre pelo menos 2 canais.</div>
                </div>
            ';
        } else {
            $html .= '
                <div style="padding:14px;border:1px solid #ddd;border-radius:12px;">
                    <div style="font-size:18px;font-weight:800;margin-bottom:10px;">Resumo por canal</div>
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
                        <td style="padding:10px 8px;">' . htmlspecialchars($c['channel']) . '</td>
                        <td style="padding:10px 8px;">' . number_format($c['spend'], 2, ',', '.') . '</td>
                        <td style="padding:10px 8px;">' . $c['conv'] . '</td>
                        <td style="padding:10px 8px;">' . ($c['cpa'] === null ? '—' : number_format($c['cpa'], 2, ',', '.')) . '</td>
                    </tr>
                ';
            }
            $html .= '</table></div>';

            // recomendação + botões simular
            if ($best && $worst && $best['channel'] !== $worst['channel'] && $best['cpa'] > 0 && $worst['cpa'] !== null) {
                $ratio = $worst['cpa'] / $best['cpa'];

                if ($ratio >= 1.3) {
                    $html .= '
                        <div style="padding:14px;border:1px solid #ddd;border-radius:12px;">
                            <div style="font-size:18px;font-weight:900;">Recomendação</div>
                            <div style="margin-top:6px;">Mover verba de <b>' . htmlspecialchars($worst['channel']) . '</b> → <b>' . htmlspecialchars($best['channel']) . '</b></div>
                            <div style="margin-top:6px;color:#555;">CPA de ' . htmlspecialchars($worst['channel']) . ' é ~' . number_format(($ratio - 1) * 100, 0) . '% maior.</div>

                            <div style="margin-top:10px;display:flex;gap:10px;flex-wrap:wrap;">
                                <a href="/simulate?from=' . urlencode($worst['channel']) . '&to=' . urlencode($best['channel']) . '&pct=10&days=14" style="padding:9px 12px;border:1px solid #e5e5e5;border-radius:10px;text-decoration:none;color:#111;">Simular 10%</a>
                                <a href="/simulate?from=' . urlencode($worst['channel']) . '&to=' . urlencode($best['channel']) . '&pct=20&days=14" style="padding:9px 12px;border:1px solid #e5e5e5;border-radius:10px;text-decoration:none;color:#111;">Simular 20%</a>
                                <a href="/simulate?from=' . urlencode($worst['channel']) . '&to=' . urlencode($best['channel']) . '&pct=30&days=14" style="padding:9px 12px;border:1px solid #e5e5e5;border-radius:10px;text-decoration:none;color:#111;">Simular 30%</a>
                            </div>
                        </div>
                    ';
                } else {
                    $html .= '
                        <div style="padding:14px;border:1px solid #ddd;border-radius:12px;">
                            <div style="font-size:18px;font-weight:900;">Recomendação</div>
                            <div style="margin-top:6px;">Manter por enquanto (diferença de CPA pequena).</div>
                            <div style="margin-top:6px;color:#555;">Dica: aumente volume / dias para ter mais confiança.</div>
                        </div>
                    ';
                }
            } else {
                $html .= '
                    <div style="padding:14px;border:1px solid #ddd;border-radius:12px;">
                        <b>Recomendação:</b> Dados insuficientes (precisa de pelo menos 2 canais com conversions > 0).
                    </div>
                ';
            }
        }

        $html .= '
            </div>
        </div>';

        return $html;
    })->name('dashboard');

    /**
     * EMPRESA
     */
    Route::get('/company/create', function () {
        return topbar_html() . '
        <div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:720px; margin:18px auto; padding:0 14px;">
            <h1>Criar empresa</h1>
            <form method="POST" action="/company/create" style="margin-top:14px;">
                <input type="hidden" name="_token" value="' . csrf_token() . '">
                <label>Nome da empresa</label><br>
                <input name="name" required style="padding:10px; width:100%; max-width:420px; border:1px solid #ddd; border-radius:12px;"><br><br>
                <button type="submit" style="padding:10px 14px;border:1px solid #333;border-radius:12px;background:#111;color:#fff;cursor:pointer;">
                    Salvar
                </button>
            </form>
        </div>';
    })->name('company.create');

    Route::post('/company/create', function (Request $request) {
        $request->validate(['name' => 'required|min:2|max:120']);

        ensure_companies_table();
        ensure_company_id_column();

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
     * MÉTRICAS
     */
    Route::get('/metrics/create', function () {
        return topbar_html('metrics') . '
        <div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:720px; margin:18px auto; padding:0 14px;">
            <h1>Registrar métricas</h1>

            <form method="POST" action="/metrics/create" style="margin-top:14px;">
                <input type="hidden" name="_token" value="' . csrf_token() . '">

                <label>Data</label><br>
                <input type="date" name="date" required style="padding:10px;border:1px solid #ddd;border-radius:12px;"><br><br>

                <label>Canal</label><br>
                <input name="channel" placeholder="Google / Meta / TikTok" required style="padding:10px; width:100%; max-width:420px; border:1px solid #ddd; border-radius:12px;"><br><br>

                <label>Spend</label><br>
                <input name="spend" type="number" step="0.01" required style="padding:10px; width:200px; border:1px solid #ddd; border-radius:12px;"><br><br>

                <label>Conversions</label><br>
                <input name="conversions" type="number" required style="padding:10px; width:200px; border:1px solid #ddd; border-radius:12px;"><br><br>

                <label>Revenue (opcional)</label><br>
                <input name="revenue" type="number" step="0.01" style="padding:10px; width:200px; border:1px solid #ddd; border-radius:12px;"><br><br>

                <button type="submit" style="padding:10px 14px;border:1px solid #333;border-radius:12px;background:#111;color:#fff;cursor:pointer;">
                    Salvar
                </button>
            </form>
        </div>';
    })->name('metrics.create');

    Route::post('/metrics/create', function (Request $request) {

        $request->validate([
            'date' => 'required',
            'channel' => 'required|min:1|max:100',
            'spend' => 'required|numeric|min:0',
            'conversions' => 'required|integer|min:0',
            'revenue' => 'nullable|numeric|min:0',
        ]);

        ensure_daily_metrics_table();

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
     * ANÁLISE (com botões de simulação)
     */
    Route::get('/analysis', function () {

        ensure_company_id_column();

        $userId = auth()->id();
        $user = DB::table('users')->where('id', $userId)->first();
        $companyId = $user->company_id ?? null;

        if (!$companyId) {
            return redirect('/company/create');
        }

        $summary = get_channel_summary($companyId, 14);
        $channels = $summary['channels'];
        $best = $summary['best'];
        $worst = $summary['worst'];

        $html = topbar_html('analysis') . '
        <div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:1100px; margin:18px auto; padding:0 14px;">
            <h1>Análise (últimos ' . $summary['windowDays'] . ' dias)</h1>
            <p style="color:#666;">' . $summary['start'] . ' → ' . $summary['end'] . '</p>

            <div style="margin-top:12px;display:grid;grid-template-columns:1fr;gap:12px;">
        ';

        if (count($channels) === 0) {
            $html .= '
                <div style="padding:14px;border:1px solid #ddd;border-radius:12px;">
                    <b>Nenhuma métrica ainda.</b>
                    <div style="margin-top:6px;color:#555;">Cadastre dados em "Métricas".</div>
                </div>
            ';
        } else {

            $html .= '
                <div style="padding:14px;border:1px solid #ddd;border-radius:12px;">
                    <div style="font-size:18px;font-weight:800;margin-bottom:10px;">Resumo por canal</div>
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
                        <td style="padding:10px 8px;">' . htmlspecialchars($c['channel']) . '</td>
                        <td style="padding:10px 8px;">' . number_format($c['spend'], 2, ',', '.') . '</td>
                        <td style="padding:10px 8px;">' . $c['conv'] . '</td>
                        <td style="padding:10px 8px;">' . ($c['cpa'] === null ? '—' : number_format($c['cpa'], 2, ',', '.')) . '</td>
                    </tr>
                ';
            }
            $html .= '</table></div>';

            if ($best && $worst && $best['channel'] !== $worst['channel']) {
                $html .= '
                    <div style="padding:14px;border:1px solid #ddd;border-radius:12px;">
                        <div style="font-size:18px;font-weight:900;">Simular realocação</div>
                        <div style="margin-top:6px;color:#555;">De <b>' . htmlspecialchars($worst['channel']) . '</b> → <b>' . htmlspecialchars($best['channel']) . '</b></div>
                        <div style="margin-top:10px;display:flex;gap:10px;flex-wrap:wrap;">
                            <a href="/simulate?from=' . urlencode($worst['channel']) . '&to=' . urlencode($best['channel']) . '&pct=10&days=14" style="padding:9px 12px;border:1px solid #e5e5e5;border-radius:10px;text-decoration:none;color:#111;">10%</a>
                            <a href="/simulate?from=' . urlencode($worst['channel']) . '&to=' . urlencode($best['channel']) . '&pct=20&days=14" style="padding:9px 12px;border:1px solid #e5e5e5;border-radius:10px;text-decoration:none;color:#111;">20%</a>
                            <a href="/simulate?from=' . urlencode($worst['channel']) . '&to=' . urlencode($best['channel']) . '&pct=30&days=14" style="padding:9px 12px;border:1px solid #e5e5e5;border-radius:10px;text-decoration:none;color:#111;">30%</a>
                        </div>
                    </div>
                ';
            } else {
                $html .= '
                    <div style="padding:14px;border:1px solid #ddd;border-radius:12px;">
                        <b>Simulação:</b> cadastre pelo menos 2 canais com conversions > 0 para liberar.
                    </div>
                ';
            }
        }

        $html .= '</div></div>';

        return $html;
    })->name('analysis');

    /**
     * SIMULAÇÃO
     * - Se você entrar em /simulate sem parâmetros, ele tenta montar "pior → melhor" automaticamente.
     */
    Route::get('/simulate', function (Request $request) {

        ensure_company_id_column();

        $userId = auth()->id();
        $user = DB::table('users')->where('id', $userId)->first();
        $companyId = $user->company_id ?? null;

        if (!$companyId) {
            return redirect('/company/create');
        }

        $days = (int) ($request->get('days', 14));
        $days = max(3, min(90, $days));

        $summary = get_channel_summary($companyId, $days);

        // mapa por canal
        $byChannel = [];
        foreach ($summary['channels'] as $c) {
            $byChannel[$c['channel']] = $c;
        }

        $from = $request->get('from');
        $to = $request->get('to');
        $pct = (float) $request->get('pct', 20);
        $pct = max(1, min(80, $pct));

        // se não vier from/to, usa pior→melhor
        if (!$from) $from = $summary['worst']['channel'] ?? null;
        if (!$to)   $to   = $summary['best']['channel'] ?? null;

        if (!$from || !$to || !isset($byChannel[$from]) || !isset($byChannel[$to])) {
            $list = implode(', ', array_map('htmlspecialchars', array_keys($byChannel)));
            return topbar_html('simulate') . '
                <div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:980px; margin:18px auto; padding:0 14px;">
                    <h1>Simulação</h1>
                    <p>Dados insuficientes para simular.</p>
                    <p>Canais encontrados: <b>' . $list . '</b></p>
                    <p><a href="/analysis">Voltar</a></p>
                </div>
            ';
        }

        $fromSpend = (float) $byChannel[$from]['spend'];
        $toSpend = (float) $byChannel[$to]['spend'];

        $fromCpa = $byChannel[$from]['cpa'];
        $toCpa = $byChannel[$to]['cpa'];

        if ($fromSpend <= 0 || $fromCpa === null || $toCpa === null) {
            return topbar_html('simulate') . '
                <div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:980px; margin:18px auto; padding:0 14px;">
                    <h1>Simulação</h1>
                    <p>Precisa ter spend > 0 no canal de origem e conversions > 0 nos dois canais.</p>
                    <p><a href="/analysis">Voltar</a></p>
                </div>
            ';
        }

        $moveAmount = $fromSpend * ($pct / 100.0);

        $fromSpendNew = max(0, $fromSpend - $moveAmount);
        $toSpendNew = $toSpend + $moveAmount;

        // Assunção MVP: CPA constante
        $fromConvOld = $fromSpend / $fromCpa;
        $toConvOld = $toSpend / $toCpa;
        $fromConvNew = $fromSpendNew / $fromCpa;
        $toConvNew = $toSpendNew / $toCpa;

        $totalOld = $fromConvOld + $toConvOld;
        $totalNew = $fromConvNew + $toConvNew;

        $delta = $totalNew - $totalOld;

        $fmtMoney = fn($v) => number_format($v, 2, ',', '.');
        $fmtNum = fn($v) => number_format($v, 2, ',', '.');

        return topbar_html('simulate') . '
            <div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial; max-width:1100px; margin:18px auto; padding:0 14px;">
                <h1>Simulação</h1>
                <p style="color:#666;">Janela: ' . $summary['windowDays'] . ' dias (' . $summary['start'] . ' → ' . $summary['end'] . ')</p>

                <div style="display:grid;grid-template-columns:1fr;gap:12px;margin-top:12px;">
                    <div style="padding:14px;border:1px solid #ddd;border-radius:12px;">
                        <div style="font-size:18px;font-weight:900;">Mover ' . $pct . '%</div>
                        <div style="margin-top:6px;">De <b>' . htmlspecialchars($from) . '</b> → Para <b>' . htmlspecialchars($to) . '</b></div>
                        <div style="margin-top:6px;color:#555;">Valor movido: <b>R$ ' . $fmtMoney($moveAmount) . '</b></div>
                    </div>

                    <div style="padding:14px;border:1px solid #ddd;border-radius:12px;">
                        <div style="font-weight:900;">Antes</div>
                        <ul>
                            <li>' . htmlspecialchars($from) . ': Spend R$ ' . $fmtMoney($fromSpend) . ' | CPA ' . $fmtMoney($fromCpa) . ' | Conv est. ' . $fmtNum($fromConvOld) . '</li>
                            <li>' . htmlspecialchars($to) . ': Spend R$ ' . $fmtMoney($toSpend) . ' | CPA ' . $fmtMoney($toCpa) . ' | Conv est. ' . $fmtNum($toConvOld) . '</li>
                            <li><b>Total conv est.: ' . $fmtNum($totalOld) . '</b></li>
                        </ul>
                    </div>

                    <div style="padding:14px;border:1px solid #ddd;border-radius:12px;">
                        <div style="font-weight:900;">Depois</div>
                        <ul>
                            <li>' . htmlspecialchars($from) . ': Spend R$ ' . $fmtMoney($fromSpendNew) . ' | CPA ' . $fmtMoney($fromCpa) . ' | Conv est. ' . $fmtNum($fromConvNew) . '</li>
                            <li>' . htmlspecialchars($to) . ': Spend R$ ' . $fmtMoney($toSpendNew) . ' | CPA ' . $fmtMoney($toCpa) . ' | Conv est. ' . $fmtNum($toConvNew) . '</li>
                            <li><b>Total conv est.: ' . $fmtNum($totalNew) . '</b></li>
                        </ul>
                    </div>

                    <div style="padding:14px;border:1px solid #ddd;border-radius:12px;">
                        <div style="font-size:18px;font-weight:900;">Impacto estimado</div>
                        <div style="margin-top:6px;">Δ conversões (estimado): <b>' . $fmtNum($delta) . '</b></div>
                        <div style="margin-top:6px;color:#666;font-size:13px;">
                            Assunção do MVP: CPA permanece constante ao mover budget.
                        </div>
                    </div>

                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <a href="/simulate?from=' . urlencode($from) . '&to=' . urlencode($to) . '&pct=10&days=' . $summary['windowDays'] . '" style="padding:9px 12px;border:1px solid #e5e5e5;border-radius:10px;text-decoration:none;color:#111;">10%</a>
                        <a href="/simulate?from=' . urlencode($from) . '&to=' . urlencode($to) . '&pct=20&days=' . $summary['windowDays'] . '" style="padding:9px 12px;border:1px solid #e5e5e5;border-radius:10px;text-decoration:none;color:#111;">20%</a>
                        <a href="/simulate?from=' . urlencode($from) . '&to=' . urlencode($to) . '&pct=30&days=' . $summary['windowDays'] . '" style="padding:9px 12px;border:1px solid #e5e5e5;border-radius:10px;text-decoration:none;color:#111;">30%</a>
                    </div>
                </div>
            </div>
        ';
    })->name('simulate');

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
