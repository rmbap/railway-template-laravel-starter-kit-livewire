<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class BudgetEngineService
{
    public function getChannelPerformance(int $companyId, int $windowDays = 14): array
    {
        $end = now()->toDateString();
        $start = now()->subDays($windowDays - 1)->toDateString();

        $rows = DB::table('daily_metrics')
            ->selectRaw('channel, SUM(spend) as spend_sum, SUM(conversions) as conv_sum, SUM(COALESCE(revenue,0)) as revenue_sum')
            ->where('company_id', $companyId)
            ->whereBetween('date', [$start, $end])
            ->groupBy('channel')
            ->orderBy('channel')
            ->get();

        $channels = [];

        foreach ($rows as $r) {
            $spend = (float) $r->spend_sum;
            $conv = (int) $r->conv_sum;
            $revenue = (float) $r->revenue_sum;

            $cpa = $conv > 0 ? $spend / $conv : null;
            $roas = $spend > 0 ? $revenue / $spend : null;
            $valuePerConversion = $conv > 0 ? $revenue / $conv : null;
            $leadsPerReal = $spend > 0 ? $conv / $spend : null;

            $channels[] = [
                'channel' => $r->channel,
                'spend' => $spend,
                'conv' => $conv,
                'revenue' => $revenue,
                'cpa' => $cpa,
                'roas' => $roas,
                'value_per_conversion' => $valuePerConversion,
                'leads_per_real' => $leadsPerReal,
            ];
        }

        return [
            'window_days' => $windowDays,
            'window_start' => $start,
            'window_end' => $end,
            'channels' => $channels,
        ];
    }

    public function getBestAndWorstChannels(array $channels): array
    {
        $withCpa = array_values(array_filter($channels, fn ($x) => $x['cpa'] !== null));

        if (count($withCpa) === 0) {
            return [
                'best' => null,
                'worst' => null,
            ];
        }

        usort($withCpa, fn ($a, $b) => $a['cpa'] <=> $b['cpa']);

        return [
            'best' => $withCpa[0] ?? null,
            'worst' => $withCpa[count($withCpa) - 1] ?? null,
        ];
    }

    public function getExecutiveSummary(array $channels): array
    {
        $bestCpa = null;
        $bestRoas = null;
        $bestValuePerLead = null;

        $withCpa = array_values(array_filter($channels, fn ($x) => $x['cpa'] !== null));
        if (count($withCpa) > 0) {
            usort($withCpa, fn ($a, $b) => $a['cpa'] <=> $b['cpa']);
            $bestCpa = $withCpa[0];
        }

        $withRoas = array_values(array_filter($channels, fn ($x) => $x['roas'] !== null));
        if (count($withRoas) > 0) {
            usort($withRoas, fn ($a, $b) => $b['roas'] <=> $a['roas']);
            $bestRoas = $withRoas[0];
        }

        $withValuePerLead = array_values(array_filter($channels, fn ($x) => $x['value_per_conversion'] !== null));
        if (count($withValuePerLead) > 0) {
            usort($withValuePerLead, fn ($a, $b) => $b['value_per_conversion'] <=> $a['value_per_conversion']);
            $bestValuePerLead = $withValuePerLead[0];
        }

        return [
            'best_cpa' => $bestCpa,
            'best_roas' => $bestRoas,
            'best_value_per_lead' => $bestValuePerLead,
        ];
    }

    public function buildRecommendation(array $channels, int $pctMove = 20): array
    {
        $ranking = $this->getBestAndWorstChannels($channels);

        $best = $ranking['best'];
        $worst = $ranking['worst'];

        if (!$best || !$worst || $best['channel'] === $worst['channel']) {
            return [
                'has_recommendation' => false,
                'decision_text' => 'Dados insuficientes',
                'expected_leads_gain' => 0,
                'expected_revenue_gain' => 0,
                'best' => $best,
                'worst' => $worst,
            ];
        }

        $ratio = $best['cpa'] > 0 ? ($worst['cpa'] / $best['cpa']) : null;

        if ($ratio === null || $ratio < 1.3) {
            return [
                'has_recommendation' => false,
                'decision_text' => 'Manter (diferença pequena)',
                'expected_leads_gain' => 0,
                'expected_revenue_gain' => 0,
                'best' => $best,
                'worst' => $worst,
            ];
        }

        $simulation = $this->simulateMoveBetweenChannels(
            $channels,
            $worst['channel'],
            $best['channel'],
            $pctMove
        );

        return [
            'has_recommendation' => true,
            'decision_text' => 'Reduzir ' . $worst['channel'] . ' e aumentar ' . $best['channel'],
            'ratio' => $ratio,
            'pct_move' => $pctMove,
            'expected_leads_gain' => $simulation['delta_leads'],
            'expected_revenue_gain' => $simulation['delta_revenue'],
            'best' => $best,
            'worst' => $worst,
            'simulation' => $simulation,
        ];
    }

    public function simulateMoveBetweenChannels(array $channels, string $from, string $to, float $pct): array
    {
        $data = [];

        foreach ($channels as $channel) {
            $data[$channel['channel']] = $channel;
        }

        if (!isset($data[$from]) || !isset($data[$to])) {
            return [
                'valid' => false,
                'message' => 'Canais inválidos',
                'delta_leads' => 0,
                'delta_revenue' => 0,
            ];
        }

        if (!$data[$from]['cpa'] || !$data[$to]['cpa']) {
            return [
                'valid' => false,
                'message' => 'Um dos canais não possui conversões suficientes',
                'delta_leads' => 0,
                'delta_revenue' => 0,
            ];
        }

        $move = $data[$from]['spend'] * ($pct / 100);

        $fromNew = $data[$from]['spend'] - $move;
        $toNew = $data[$to]['spend'] + $move;

        $convBefore =
            ($data[$from]['spend'] / $data[$from]['cpa']) +
            ($data[$to]['spend'] / $data[$to]['cpa']);

        $convAfter =
            ($fromNew / $data[$from]['cpa']) +
            ($toNew / $data[$to]['cpa']);

        $revenueBefore =
            (($data[$from]['spend'] / $data[$from]['cpa']) * ($data[$from]['value_per_conversion'] ?? 0)) +
            (($data[$to]['spend'] / $data[$to]['cpa']) * ($data[$to]['value_per_conversion'] ?? 0));

        $revenueAfter =
            (($fromNew / $data[$from]['cpa']) * ($data[$from]['value_per_conversion'] ?? 0)) +
            (($toNew / $data[$to]['cpa']) * ($data[$to]['value_per_conversion'] ?? 0));

        return [
            'valid' => true,
            'message' => null,
            'from' => $from,
            'to' => $to,
            'pct' => $pct,
            'from_old_spend' => $data[$from]['spend'],
            'to_old_spend' => $data[$to]['spend'],
            'from_new_spend' => $fromNew,
            'to_new_spend' => $toNew,
            'conv_before' => $convBefore,
            'conv_after' => $convAfter,
            'revenue_before' => $revenueBefore,
            'revenue_after' => $revenueAfter,
            'delta_leads' => $convAfter - $convBefore,
            'delta_revenue' => $revenueAfter - $revenueBefore,
        ];
    }

    public function buildPlanner(array $channels, float $totalBudget, string $goal): array
    {
        $plannerChannels = [];
        $totalScore = 0;

        foreach ($channels as $channel) {
            if ($channel['spend'] <= 0) {
                continue;
            }

            $score = $goal === 'revenue'
                ? ($channel['roas'] ?? 0)
                : ($channel['leads_per_real'] ?? 0);

            if ($score <= 0) {
                $score = 0.01;
            }

            $plannerChannels[] = [
                'channel' => $channel['channel'],
                'roas' => $channel['roas'] ?? 0,
                'leads_per_real' => $channel['leads_per_real'] ?? 0,
                'score' => $score,
            ];

            $totalScore += $score;
        }

        if (count($plannerChannels) === 0 || $totalScore <= 0) {
            return [
                'valid' => false,
                'channels' => [],
                'estimated_total_revenue' => 0,
                'estimated_total_leads' => 0,
            ];
        }

        $estimatedTotalRevenue = 0;
        $estimatedTotalLeads = 0;

        foreach ($plannerChannels as &$channel) {
            $allocationPct = $channel['score'] / $totalScore;
            $allocatedBudget = $totalBudget * $allocationPct;
            $estimatedRevenue = $allocatedBudget * $channel['roas'];
            $estimatedLeads = $allocatedBudget * $channel['leads_per_real'];

            $channel['allocation_pct'] = $allocationPct * 100;
            $channel['allocated_budget'] = $allocatedBudget;
            $channel['estimated_revenue'] = $estimatedRevenue;
            $channel['estimated_leads'] = $estimatedLeads;

            $estimatedTotalRevenue += $estimatedRevenue;
            $estimatedTotalLeads += $estimatedLeads;
        }

        unset($channel);

        usort($plannerChannels, fn ($a, $b) => $b['allocated_budget'] <=> $a['allocated_budget']);

        return [
            'valid' => true,
            'channels' => $plannerChannels,
            'estimated_total_revenue' => $estimatedTotalRevenue,
            'estimated_total_leads' => $estimatedTotalLeads,
        ];
    }
}
