<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    protected string $adminEmail = 'rodrigo.baptista@gmail.com';

    protected function ensureUsersSchema(): void
    {
        $companyColumn = DB::select("SHOW COLUMNS FROM users LIKE 'company_id'");
        if (count($companyColumn) === 0) {
            DB::statement("ALTER TABLE users ADD company_id BIGINT UNSIGNED NULL");
        }

        $adminColumn = DB::select("SHOW COLUMNS FROM users LIKE 'is_admin'");
        if (count($adminColumn) === 0) {
            DB::statement("ALTER TABLE users ADD is_admin TINYINT(1) NOT NULL DEFAULT 0");
        }
    }

    protected function ensureCompaniesSchema(): void
    {
        DB::statement("CREATE TABLE IF NOT EXISTS companies (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            owner_user_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )");

        $columns = [
            'segment' => "ALTER TABLE companies ADD segment VARCHAR(120) NULL",
            'region' => "ALTER TABLE companies ADD region VARCHAR(120) NULL",
            'avg_ticket' => "ALTER TABLE companies ADD avg_ticket DECIMAL(12,2) NULL",
            'primary_goal' => "ALTER TABLE companies ADD primary_goal VARCHAR(120) NULL",
            'monthly_budget' => "ALTER TABLE companies ADD monthly_budget DECIMAL(12,2) NULL",
        ];

        foreach ($columns as $column => $sql) {
            $exists = DB::select("SHOW COLUMNS FROM companies LIKE '{$column}'");
            if (count($exists) === 0) {
                DB::statement($sql);
            }
        }
    }

    protected function ensureDailyMetricsSchema(): void
    {
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
    }

    protected function ensureRecommendationsSchema(): void
    {
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
    }

    protected function promoteAdminIfNeeded()
    {
        $userId = auth()->id();

        if (!$userId) {
            return null;
        }

        $user = DB::table('users')->where('id', $userId)->first();

        if ($user && $user->email === $this->adminEmail && (int) ($user->is_admin ?? 0) !== 1) {
            DB::table('users')->where('id', $userId)->update([
                'is_admin' => 1,
            ]);

            $user = DB::table('users')->where('id', $userId)->first();
        }

        return $user;
    }

    protected function guardAdmin()
    {
        $this->ensureUsersSchema();
        $this->ensureCompaniesSchema();
        $this->ensureDailyMetricsSchema();
        $this->ensureRecommendationsSchema();

        $user = $this->promoteAdminIfNeeded();

        if (!$user || empty($user->is_admin)) {
            abort(403, 'Acesso negado');
        }

        return $user;
    }

    public function index()
    {
        $this->guardAdmin();

        $companies = DB::table('companies')
            ->leftJoin('users', 'companies.owner_user_id', '=', 'users.id')
            ->select(
                'companies.id',
                'companies.name',
                'companies.segment',
                'companies.region',
                'companies.avg_ticket',
                'companies.primary_goal',
                'companies.monthly_budget',
                'users.name as owner_name',
                'users.email as owner_email'
            )
            ->orderByDesc('companies.id')
            ->get();

        $rows = [];

        foreach ($companies as $company) {
            $rows[] = [
                'id' => $company->id,
                'name' => $company->name,
                'segment' => $company->segment,
                'region' => $company->region,
                'avg_ticket' => (float) ($company->avg_ticket ?? 0),
                'primary_goal' => $company->primary_goal,
                'monthly_budget' => (float) ($company->monthly_budget ?? 0),
                'owner_name' => $company->owner_name,
                'owner_email' => $company->owner_email,
                'users_count' => DB::table('users')->where('company_id', $company->id)->count(),
                'metrics_count' => DB::table('daily_metrics')->where('company_id', $company->id)->count(),
                'recommendations_count' => DB::table('recommendations')->where('company_id', $company->id)->count(),
            ];
        }

        return view('admin.index', [
            'companies' => $rows,
        ]);
    }

    public function show(int $id)
    {
        $this->guardAdmin();

        $company = DB::table('companies')->where('id', $id)->first();

        if (!$company) {
            abort(404, 'Empresa não encontrada');
        }

        $users = DB::table('users')
            ->where('company_id', $id)
            ->orderBy('name')
            ->get();

        $metrics = DB::table('daily_metrics')
            ->where('company_id', $id)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        $recommendations = DB::table('recommendations')
            ->where('company_id', $id)
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        return view('admin.company', [
            'company' => $company,
            'users' => $users,
            'metrics' => $metrics,
            'recommendations' => $recommendations,
        ]);
    }
}
