<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\BudgetEngineService;

class PlannerController extends Controller
{
    public function form()
    {
        return view('planner.form');
    }

    public function run(Request $request, BudgetEngineService $engine)
    {
        $request->validate([
            'total_budget' => 'required|numeric|min:1',
            'goal' => 'required|in:revenue,leads',
        ]);

        $userId = auth()->id();

        $user = DB::table('users')
            ->where('id', $userId)
            ->first();

        $companyId = $user->company_id ?? null;

        if (!$companyId) {
            return redirect('/company/create');
        }

        $totalBudget = (float) $request->total_budget;
        $goal = $request->goal;

        $performance = $engine->getChannelPerformance($companyId, 14);
        $channels = $performance['channels'];

        $planner = $engine->buildPlanner($channels, $totalBudget, $goal);

        if (!$planner['valid']) {
            return view('planner.empty');
        }

        return view('planner.result', [
            'goal' => $goal,
            'totalBudget' => $totalBudget,
            'channels' => $planner['channels'],
            'estimatedTotalRevenue' => $planner['estimated_total_revenue'],
            'estimatedTotalLeads' => $planner['estimated_total_leads'],
        ]);
    }
}
