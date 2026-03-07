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

        $performance = $engine->getChannelPerformance($companyId, 14);

        $channels = $performance['channels'] ?? [];
        $window_start = $performance['window_start'] ?? null;
        $window_end = $performance['window_end'] ?? null;

        $recommendation = $engine->buildRecommendation($channels);

        return view('analysis.index', [
            'channels' => $channels,
            'window_start' => $window_start,
            'window_end' => $window_end,
            'recommendation' => $recommendation,
        ]);
    }
}
