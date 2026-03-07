<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\BudgetEngineService;

class RecommendationController extends Controller
{
    public function store(Request $request, BudgetEngineService $engine)
    {
        $userId = auth()->id();

        $user = DB::table('users')
            ->where('id', $userId)
            ->first();

        $companyId = $user->company_id ?? null;

        if (!$companyId) {
            return redirect('/company/create');
        }

        DB::statement("CREATE TABLE IF NOT EXISTS recommendations (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            company_id BIGINT UNSIGNED NOT NULL,
            window_days INT NOT NULL DEFAULT 14,
            snapshot_json LONGTEXT NULL,
            decision_text VARCHAR(255) NOT NULL,
            expected_leads_gain DECIMAL(10,2) NULL,
            expected_revenue_gain DECIMAL(10,2) NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )");

        $windowDays = (int) ($request->window_days ?? 14);

        $performance = $engine->getChannelPerformance($companyId, $windowDays);
        $channels = $performance['channels'];
        $recommendation = $engine->buildRecommendation($channels, 20);

        $snapshot = [
            'window_days' => $performance['window_days'],
            'window_start' => $performance['window_start'],
            'window_end' => $performance['window_end'],
            'channels' => $channels,
            'recommendation' => $recommendation,
        ];

        DB::table('recommendations')->insert([
            'company_id' => $companyId,
            'window_days' => $performance['window_days'],
            'snapshot_json' => json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'decision_text' => $recommendation['decision_text'] ?? 'Sem decisão',
            'expected_leads_gain' => $recommendation['expected_leads_gain'] ?? 0,
            'expected_revenue_gain' => $recommendation['expected_revenue_gain'] ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('/analysis');
    }
}
