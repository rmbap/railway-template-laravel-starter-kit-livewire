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

        $performance = $engine->getChannelPerformance($companyId, 14);
        $channels = $performance['channels'] ?? [];

        $planner = $engine->buildPlanner(
            $channels,
            (float) $request->total_budget,
            $request->goal
        );

        return view('planner.result', [
            'goal' => $request->goal,
            'totalBudget' => (float) $request->total_budget,
            'planner' => $planner,
        ]);
    }
}
