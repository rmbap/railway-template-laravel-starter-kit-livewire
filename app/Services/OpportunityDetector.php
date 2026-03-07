<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class OpportunityDetector
{
    public function detectForCompany(int $companyId)
    {
        $engine = new BudgetEngineService();

        $performance = $engine->getChannelPerformance($companyId, 14);

        $channels = $performance['channels'] ?? [];

        if (count($channels) === 0) {
            return;
        }

        $recommendation = $engine->buildRecommendation($channels);

        if (!$recommendation['has_recommendation']) {
            return;
        }

        DB::table('recommendations')->insert([
            'company_id' => $companyId,
            'window_days' => 14,
            'snapshot_json' => json_encode($recommendation),
            'decision_text' => $recommendation['decision_text'],
            'expected_leads_gain' => $recommendation['expected_leads_gain'],
            'expected_revenue_gain' => $recommendation['expected_revenue_gain'],
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
