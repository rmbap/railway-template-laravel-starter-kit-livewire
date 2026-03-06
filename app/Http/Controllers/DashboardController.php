<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Services\BudgetEngineService;

class DashboardController extends Controller
{
    public function index(BudgetEngineService $engine)
    {
        $userId = auth()->id();

        $user = DB::table('users')
            ->where('id', $userId)
            ->first();

        if (!$user || !$user->company_id) {
            return redirect('/company/create');
        }

        $companyId = $user->company_id;

        $performance = $engine->getChannelPerformance($companyId, 14);

        $channels = $performance['channels'];

        $summary = $engine->getExecutiveSummary($channels);

        $recommendation = $engine->buildRecommendation($channels);

        return view('dashboard.index', [
            'channels' => $channels,
            'summary' => $summary,
            'recommendation' => $recommendation,
            'window_start' => $performance['window_start'],
            'window_end' => $performance['window_end'],
        ]);
    }
}
