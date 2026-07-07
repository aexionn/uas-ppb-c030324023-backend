<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    private const RULES = [
        'program_id' => ['required', 'exists:programs,id'],
        'full_name' => ['required', 'string', 'max:255'],
        'birth_place' => ['required', 'string', 'max:255'],
        'birth_date' => ['required', 'date'],
        'gender' => ['required', 'in:L,P'],
        'address' => ['required', 'string'],
        'phone' => ['required', 'string', 'max:20'],
        'school_origin' => ['required', 'string', 'max:255'],
        'father_name' => ['required', 'string', 'max:255'],
        'father_job' => ['required', 'string', 'max:255'],
        'mother_name' => ['required', 'string', 'max:255'],
        'mother_job' => ['required', 'string', 'max:255'],
        'parents_income' => ['required', 'in:<1jt,1-3jt,3-5jt,>5jt'],
        'photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
    ];

    public function create(Request $request)
    {
        if (Application::where('account_id', $request->user()->id)->exists()) {
            return redirect('/application');
        }

        return view('application.create', ['programs' => Program::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        if (Application::where('account_id', $request->user()->id)->exists()) {
            return redirect('/application');
        }

        $data = $request->validate(self::RULES);
        $data['photo_path'] = $request->file('photo')->store('photos', 'public');
        $data['account_id'] = $request->user()->id;
        $data['last_submitted_at'] = now();
        $data['status'] = 'submitted';

        Application::create($data);

        return redirect('/application')->with('status', 'Pendaftaran berhasil dikirim.');
    }

    public function show(Request $request)
    {
        $application = Application::where('account_id', $request->user()->id)->first();

        if (! $application) {
            return redirect('/application/create');
        }

        return view('application.show', ['application' => $application]);
    }

    public function edit(Request $request)
    {
        $application = Application::where('account_id', $request->user()->id)->first();

        if (! $application) {
            return redirect('/application/create');
        }

        if ($application->isLocked()) {
            return redirect('/application')->with('status', 'Pendaftaran terkunci, tidak dapat diedit.');
        }

        return view('application.edit', [
            'application' => $application,
            'programs' => Program::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $application = Application::where('account_id', $request->user()->id)->first();

        if (! $application) {
            return redirect('/application/create');
        }

        if ($application->isLocked()) {
            return redirect('/application')->with('status', 'Pendaftaran terkunci, tidak dapat diedit.');
        }

        $rules = self::RULES;
        $rules['photo'] = ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'];
        $data = $request->validate($rules);

        if ($request->hasFile('photo')) {
            Storage::disk('public')->delete($application->photo_path);
            $data['photo_path'] = $request->file('photo')->store('photos', 'public');
        }

        $data['edits_used'] = $application->edits_used + 1;
        $data['last_submitted_at'] = now();

        $application->update($data);

        return redirect('/application')->with('status', 'Perubahan disimpan.');
    }
}
