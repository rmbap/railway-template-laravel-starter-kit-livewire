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

        $user = DB::table('users')
            ->where('id', $userId)
            ->first();

        $companyId = $user->company_id ?? null;

        if (!$companyId) {
            return redirect('/company/create');
        }

        $windowDays = 14;

        $end = now()->toDateString();
        $start = now()->subDays($windowDays - 1)->toDateString();

        $rows = DB::table('daily_metrics')
            ->selectRaw('channel,
                SUM(spend) as spend_sum,
                SUM(conversions) as conv_sum,
                SUM(COALESCE(revenue,0)) as revenue_sum')
            ->where('company_id', $companyId)
            ->whereBetween('date', [$start, $end])
            ->groupBy('channel')
            ->orderBy('channel')
            ->get();

        if ($rows->count() === 0) {

            return view('analysis.empty', [
                'windowDays' => $windowDays
            ]);
        }

        $channels = [];

        foreach ($rows as $r) {

            $spend = (float) $r->spend_sum;
            $conv = (int) $r->conv_sum;
            $rev = (float) $r->revenue_sum;

            $cpa = $conv > 0 ? $spend / $conv : null;
            $roas = $spend > 0 ? $rev / $spend : null;

            $valuePerConversion = $conv > 0 ? $rev / $conv : null;

            $channels[] = [
                'channel' => $r->channel,
                'spend' => $spend,
                'conv' => $conv,
                'revenue' => $rev,
                'cpa' => $cpa,
                'roas' => $roas,
                'value_per_conversion' => $valuePerConversion
            ];
        }

        $withCpa = array_values(
            array_filter($channels, fn($x) => $x['cpa'] !== null)
        );

        usort($withCpa, fn($a, $b) => $a['cpa'] <=> $b['cpa']);

        $best = $withCpa[0] ?? null;
        $worst = $withCpa[count($withCpa) - 1] ?? null;

        return view('analysis.index', [
            'channels' => $channels,
            'best' => $best,
            'worst' => $worst,
            'windowDays' => $windowDays,
            'start' => $start,
            'end' => $end
        ]);
    }
}
