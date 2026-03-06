<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function create()
    {
        return view('company.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:2|max:120'
        ]);

        DB::statement("CREATE TABLE IF NOT EXISTS companies (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            owner_user_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )");

        $columns = DB::select("SHOW COLUMNS FROM users LIKE 'company_id'");

        if (count($columns) === 0) {
            DB::statement("ALTER TABLE users ADD company_id BIGINT UNSIGNED NULL");
        }

        $userId = auth()->id();

        DB::table('companies')->insert([
            'name' => $request->name,
            'owner_user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $companyId = DB::getPdo()->lastInsertId();

        DB::table('users')
            ->where('id', $userId)
            ->update([
                'company_id' => $companyId
            ]);

        return redirect('/dashboard');
    }
}
