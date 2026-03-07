<?php

namespace App\Http\Controllers;

class AdminController extends Controller
{
    public function index()
    {
        return 'Admin funcionando';
    }

    public function show($id)
    {
        return 'Admin empresa funcionando: ' . $id;
    }
}
