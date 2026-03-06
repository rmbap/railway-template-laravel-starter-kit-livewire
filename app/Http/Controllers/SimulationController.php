<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SimulationController extends Controller
{
    public function run(Request $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $pct = (float) $request->get('pct', 20);

        $userId = auth()->id();

        $user = DB::table('users')
            ->where('id', $userId)
            ->first();

        $companyId = $user->company_id ?? null;

        $rows = DB::table('daily_metrics')
            ->selectRaw('channel,
                SUM(spend) as spend_sum,
                SUM(conversions) as conv_sum,
                SUM(COALESCE(revenue,0)) as revenue_sum')
            ->where('company_id', $companyId)
            ->groupBy('channel')
            ->get();

        $data = [];

        foreach ($rows as $r) {

            $spend = (float) $r->spend_sum;
            $conv = (int) $r->conv_sum;
            $rev = (float) $r->revenue_sum;

            $data[$r->channel] = [
                'spend' => $spend,
                'conv' => $conv,
                'revenue' => $rev,
                'cpa' => $conv > 0 ? $spend / $conv : null,
                'value_per_conversion' => $conv > 0 ? $rev / $conv : 0
            ];
        }

        if (!isset($data[$from]) || !isset($data[$to])) {
            return "Canais inválidos";
        }

        if (!$data[$from]['cpa'] || !$data[$to]['cpa']) {
            return "Não é possível simular porque um dos canais não possui conversões suficientes.";
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

        $revenueBefore =
            ($data[$from]['spend'] / $data[$from]['cpa']) * $data[$from]['value_per_conversion'] +
            ($data[$to]['spend'] / $data[$to]['cpa']) * $data[$to]['value_per_conversion'];

        $revenueAfter =
            ($fromNew / $data[$from]['cpa']) * $data[$from]['value_per_conversion'] +
            ($toNew / $data[$to]['cpa']) * $data[$to]['value_per_conversion'];

        $deltaLeads = $convAfter - $convBefore;
        $deltaRevenue = $revenueAfter - $revenueBefore;

        return view('simulation.result', [
            'from' => $from,
            'to' => $to,
            'pct' => $pct,
            'beforeRevenue' => $revenueBefore,
            'afterRevenue' => $revenueAfter,
            'beforeLeads' => $convBefore,
            'afterLeads' => $convAfter,
            'deltaLeads' => $deltaLeads,
            'deltaRevenue' => $deltaRevenue,
            'data' => $data,
            'fromNew' => $fromNew,
            'toNew' => $toNew
        ]);
    }
}
