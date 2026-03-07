<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PlannerController extends Controller
{
    public function form()
    {
        return 'Planner GET funcionando';
    }

    public function run(Request $request)
    {
        return 'Planner POST funcionando';
    }
}
