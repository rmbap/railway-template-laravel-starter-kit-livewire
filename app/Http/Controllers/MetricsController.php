<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetricsController extends Controller
{
    public function create()
    {
        return view('metrics.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required',
            'channel' => 'required|min:1|max:100',
            'spend' => 'required|numeric|min:0',
            'conversions' => 'required|integer|min:0',
            'revenue' => 'nullable|numeric|min:0',
        ]);

        DB::statement("CREATE TABLE IF NOT EXISTS daily_metrics (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            company_id BIGINT UNSIGNED NOT NULL,
            date DATE NOT NULL,
            channel VARCHAR(100) NOT NULL,
            spend DECIMAL(10,2) NOT NULL,
            conversions INT NOT NULL,
            revenue DECIMAL(10,2) NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )");

        $userId = auth()->id();

        $user = DB::table('users')
            ->where('id', $userId)
            ->first();

        $companyId = $user->company_id ?? null;

        if (!$companyId) {
            return redirect('/company/create');
        }

        DB::table('daily_metrics')->insert([
            'company_id' => $companyId,
            'date' => $request->date,
            'channel' => $request->channel,
            'spend' => $request->spend,
            'conversions' => $request->conversions,
            'revenue' => $request->revenue,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('/analysis');
    }
}
