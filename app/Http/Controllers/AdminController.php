public function show($id)
{
    DB::statement("CREATE TABLE IF NOT EXISTS companies (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        owner_user_id BIGINT UNSIGNED NOT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    )");

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

    $company = DB::table('companies')->where('id', $id)->first();

    if (!$company) {
        abort(404);
    }

    $metrics = DB::table('daily_metrics')
        ->where('company_id', $id)
        ->orderByDesc('date')
        ->limit(50)
        ->get();

    $recommendations = DB::table('recommendations')
        ->where('company_id', $id)
        ->orderByDesc('id')
        ->limit(20)
        ->get();

    return view('admin.company', [
        'company' => $company,
        'metrics' => $metrics,
        'recommendations' => $recommendations,
    ]);
}
