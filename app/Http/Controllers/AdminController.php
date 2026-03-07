<?php

namespace App\Http\Controllers;

class AdminController extends Controller
{
    public function index()
    {
        $companies = collect([]);

        return view('admin.index', [
            'companies' => $companies
        ]);
    }

    public function show($id)
    {
        return 'Admin empresa funcionando: ' . $id;
    }
}
