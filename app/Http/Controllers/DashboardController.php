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

        $companyId = $user->company_id ?? null;

        if (!$companyId) {
            return view('dashboard.index', [
                'hasCompany' => false
            ]);
        }

        $performance = $engine->getChannelPerformance($companyId);

        $channels = $performance['channels'] ?? [];

        $recommendation = $engine->buildRecommendation($channels);

        return view('dashboard.index', [
            'hasCompany' => true,
            'channels' => $channels,
            'recommendation' => $recommendation
        ]);
    }
}
