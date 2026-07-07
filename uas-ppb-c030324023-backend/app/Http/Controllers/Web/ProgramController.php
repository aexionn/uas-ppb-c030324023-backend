<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index()
    {
        return view('programs.index', ['programs' => Program::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('programs.form', ['program' => new Program()]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => ['required', 'string', 'unique:programs,name']]);
        Program::create($request->only('name'));

        return redirect('/programs')->with('status', 'Program ditambahkan.');
    }

    public function edit(Program $program)
    {
        return view('programs.form', ['program' => $program]);
    }

    public function update(Request $request, Program $program)
    {
        $request->validate(['name' => ['required', 'string', 'unique:programs,name,'.$program->id]]);
        $program->update($request->only('name'));

        return redirect('/programs')->with('status', 'Program diperbarui.');
    }

    public function destroy(Program $program)
    {
        $program->delete();

        return redirect('/programs')->with('status', 'Program dihapus.');
    }
}
