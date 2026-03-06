<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\BudgetEngineService;

class SimulationController extends Controller
{
    public function run(Request $request, BudgetEngineService $engine)
    {

        $from = $request->get('from');
        $to = $request->get('to');
        $pct = (float) $request->get('pct', 20);

        $userId = auth()->id();

        $user = DB::table('users')
            ->where('id', $userId)
            ->first();

        $companyId = $user->company_id ?? null;

        if (!$companyId) {
            return redirect('/company/create');
        }

        /*
        |-----------------------------------
        | Busca performance dos canais
        |-----------------------------------
        */

        $performance = $engine->getChannelPerformance($companyId);

        $channels = $performance['channels'];

        /*
        |-----------------------------------
        | Executa simulação
        |-----------------------------------
        */

        $simulation = $engine->simulateMoveBetweenChannels(
            $channels,
            $from,
            $to,
            $pct
        );

        if (!$simulation['valid']) {
            return $simulation['message'];
        }

        /*
        |-----------------------------------
        | Monta estrutura usada pela view
        |-----------------------------------
        */

        $data = [];

        foreach ($channels as $channel) {
            $data[$channel['channel']] = $channel;
        }

        return view('simulation.result', [

            'from' => $from,
            'to' => $to,
            'pct' => $pct,

            'beforeRevenue' => $simulation['revenue_before'],
            'afterRevenue' => $simulation['revenue_after'],

            'beforeLeads' => $simulation['conv_before'],
            'afterLeads' => $simulation['conv_after'],

            'deltaLeads' => $simulation['delta_leads'],
            'deltaRevenue' => $simulation['delta_revenue'],

            'data' => $data,

            'fromNew' => $simulation['from_new_spend'],
            'toNew' => $simulation['to_new_spend']

        ]);
    }
}
