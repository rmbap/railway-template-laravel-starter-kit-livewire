<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        DB::statement("CREATE TABLE IF NOT EXISTS companies (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            owner_user_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )");

        $companies = DB::table('companies')
            ->orderByDesc('id')
            ->get();

        return view('admin.index', [
            'companies' => $companies
        ]);
    }

    public function show($id)
    {
        return 'Admin empresa funcionando: ' . $id;
    }
}
