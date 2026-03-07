<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\BudgetEngineService;

class RunBudgetEngine extends Command
{
    protected $signature = 'engine:run';
    protected $description = 'Run marketing budget decision engine';

    public function handle(BudgetEngineService $engine)
    {
        $this->info("Budget engine running...");

        /*
        |-----------------------------------------
        | Garantir tabela de recomendações
        |-----------------------------------------
        */

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

        /*
        |-----------------------------------------
        | Buscar empresas
        |-----------------------------------------
        */

        $companies = DB::table('companies')->get();

        if ($companies->count() === 0) {
            $this->warn("No companies found.");
            return;
        }

        foreach ($companies as $company) {

            $this->info("Processing company {$company->id}");

            /*
            |-----------------------------------------
            | Pegar performance
            |-----------------------------------------
            */

            $performance = $engine->getChannelPerformance($company->id);

            $channels = $performance['channels'];

            if (count($channels) < 2) {
                $this->warn("Company {$company->id}: insufficient data");
                continue;
            }

            /*
            |-----------------------------------------
            | Rodar recomendação
            |-----------------------------------------
            */

            $recommendation = $engine->buildRecommendation($channels);

            /*
            |-----------------------------------------
            | Snapshot para auditoria
            |-----------------------------------------
            */

            $snapshot = [
                'window_days' => $performance['window_days'],
                'window_start' => $performance['window_start'],
                'window_end' => $performance['window_end'],
                'channels' => $channels,
                'recommendation' => $recommendation
            ];

            /*
            |-----------------------------------------
            | Salvar recomendação
            |-----------------------------------------
            */

            DB::table('recommendations')->insert([
                'company_id' => $company->id,
                'window_days' => $performance['window_days'],
                'snapshot_json' => json_encode($snapshot, JSON_UNESCAPED_UNICODE),
                'decision_text' => $recommendation['decision_text'] ?? 'No decision',
                'expected_leads_gain' => $recommendation['expected_leads_gain'] ?? 0,
                'expected_revenue_gain' => $recommendation['expected_revenue_gain'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->info("Recommendation saved.");
        }

        $this->info("Budget engine finished.");
    }
}
