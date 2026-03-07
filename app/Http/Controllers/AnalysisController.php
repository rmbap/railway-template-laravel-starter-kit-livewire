<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AnalysisController extends Controller
{
    public function index()
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
            ->selectRaw('channel, SUM(spend) as spend_sum, SUM(conversions) as conv_sum, SUM(COALESCE(revenue,0)) as revenue_sum')
            ->where('company_id', $companyId)
            ->whereBetween('date', [$start, $end])
            ->groupBy('channel')
            ->orderBy('channel')
            ->get();

        $channels = [];

        foreach ($rows as $r) {
            $spend = (float) $r->spend_sum;
            $conv  = (int) $r->conv_sum;
            $rev   = (float) $r->revenue_sum;

            $cpa = $conv > 0 ? ($spend / $conv) : null;
            $roas = $spend > 0 ? ($rev / $spend) : null;
            $valuePerConversion = $conv > 0 ? ($rev / $conv) : null;

            $channels[] = [
                'channel' => $r->channel,
                'spend' => $spend,
                'conv' => $conv,
                'revenue' => $rev,
                'cpa' => $cpa,
                'roas' => $roas,
                'value_per_conversion' => $valuePerConversion,
            ];
        }

        $withCpa = array_values(array_filter($channels, fn($x) => $x['cpa'] !== null));
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
                $pctMove = 20;
                $moveAmount = $worst['spend'] * ($pctMove / 100);

                $worstNewSpend = $worst['spend'] - $moveAmount;
                $bestNewSpend  = $best['spend'] + $moveAmount;

                $convBefore =
                    ($worst['cpa'] > 0 ? $worst['spend'] / $worst['cpa'] : 0) +
                    ($best['cpa'] > 0 ? $best['spend'] / $best['cpa'] : 0);

                $convAfter =
                    ($worst['cpa'] > 0 ? $worstNewSpend / $worst['cpa'] : 0) +
                    ($best['cpa'] > 0 ? $bestNewSpend / $best['cpa'] : 0);

                $revBefore =
                    ($worst['cpa'] > 0 ? ($worst['spend'] / $worst['cpa']) * ($worst['value_per_conversion'] ?? 0) : 0) +
                    ($best['cpa'] > 0 ? ($best['spend'] / $best['cpa']) * ($best['value_per_conversion'] ?? 0) : 0);

                $revAfter =
                    ($worst['cpa'] > 0 ? ($worstNewSpend / $worst['cpa']) * ($worst['value_per_conversion'] ?? 0) : 0) +
                    ($best['cpa'] > 0 ? ($bestNewSpend / $best['cpa']) * ($best['value_per_conversion'] ?? 0) : 0);

                $expectedLeadsGain = $convAfter - $convBefore;
                $expectedRevenueGain = $revAfter - $revBefore;

                $recommendationHtml = '
                <div style="padding:14px;border:1px solid #ddd;border-radius:10px;">
                    <div style="font-size:18px;"><b>Recomendação</b></div>
                    <div style="margin-top:6px;">Mover verba de <b>'.htmlspecialchars($worst['channel']).'</b> → <b>'.htmlspecialchars($best['channel']).'</b></div>
                    <div style="margin-top:6px;color:#444;">Motivo: CPA de '.htmlspecialchars($worst['channel']).' é ~'.number_format(($ratio - 1) * 100, 0).' % maior que '.htmlspecialchars($best['channel']).'.</div>
                    <div style="margin-top:10px;color:#222;">
                        <div><b>Impacto estimado (20%)</b></div>
                        <div>+ '.number_format($expectedLeadsGain, 2, ',', '.').' leads</div>
                        <div>+ R$ '.number_format($expectedRevenueGain, 2, ',', '.').' de faturamento</div>
                    </div>
                    <div style="margin-top:10px;">
                        <a href="/simulate?from='.urlencode($worst['channel']).'&to='.urlencode($best['channel']).'&pct=10" style="margin-right:8px;">Simular 10%</a>
                        <a href="/simulate?from='.urlencode($worst['channel']).'&to='.urlencode($best['channel']).'&pct=20" style="margin-right:8px;">Simular 20%</a>
                        <a href="/simulate?from='.urlencode($worst['channel']).'&to='.urlencode($best['channel']).'&pct=30">Simular 30%</a>
                    </div>
                    <form method="POST" action="/recommendation/run" style="margin-top:10px;">
                        '.csrf_field().'
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

        return view('analysis.index', [
            'windowDays' => $windowDays,
            'start' => $start,
            'end' => $end,
            'channels' => $channels,
            'recommendationHtml' => $recommendationHtml,
        ]);
    }
}
