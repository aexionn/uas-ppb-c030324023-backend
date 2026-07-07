<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Program;

class ProgramController extends Controller
{
    public function index()
    {
        return response()->json(Program::orderBy('name')->get(['id', 'name']));
    }
}
