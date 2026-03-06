<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Services\BudgetEngineService;

class AnalysisController extends Controller
{
    public function index(BudgetEngineService $engine)
    {

        $userId = auth()->id();

        $user = DB::table('users')
            ->where('id', $userId)
            ->first();

        $companyId = $user->company_id ?? null;

        if (!$companyId) {
            return redirect('/company/create');
        }

        /*
        |------------------------------------
        | PERFORMANCE DOS CANAIS
        |------------------------------------
        */

        $performance = $engine->getChannelPerformance($companyId);

        $channels = $performance['channels'];

        /*
        |------------------------------------
        | EXECUTIVE SUMMARY
        |------------------------------------
        */

        $summary = $engine->getExecutiveSummary($channels);

        /*
        |------------------------------------
        | RECOMENDAÇÃO DO MOTOR
        |------------------------------------
        */

        $recommendation = $engine->buildRecommendation($channels);

        /*
        |------------------------------------
        | VIEW
        |------------------------------------
        */

        return view('analysis.index', [

            'channels' => $channels,

            'bestCpa' => $summary['best_cpa'],
            'bestRoas' => $summary['best_roas'],
            'bestValuePerLead' => $summary['best_value_per_lead'],

            'recommendation' => $recommendation

        ]);
    }
}
