<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminApplicationController extends Controller
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
        'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
    ];

    public function index(Request $request)
    {
        $query = Application::with(['account', 'program']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhereHas('account', fn ($a) => $a->where('nisn', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($programId = $request->query('program_id')) {
            $query->where('program_id', $programId);
        }

        return view('admin.applications.index', [
            'applications' => $query->orderByDesc('created_at')->get(),
            'programs' => Program::orderBy('name')->get(),
            'filters' => $request->only(['search', 'status', 'program_id']),
        ]);
    }

    public function show(Application $application)
    {
        return view('admin.applications.show', ['application' => $application->load(['account', 'program'])]);
    }

    public function edit(Application $application)
    {
        return view('admin.applications.edit', [
            'application' => $application,
            'programs' => Program::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Application $application)
    {
        $data = $request->validate(self::RULES);

        if ($request->hasFile('photo')) {
            Storage::disk('public')->delete($application->photo_path);
            $data['photo_path'] = $request->file('photo')->store('photos', 'public');
        }

        $application->update($data);

        return redirect("/admin/applications/{$application->id}")->with('status', 'Pendaftaran diperbarui.');
    }

    public function verdict(Request $request, Application $application)
    {
        $data = $request->validate(['status' => ['required', 'in:accepted,rejected']]);
        $application->update($data);

        return redirect("/admin/applications/{$application->id}")->with('status', 'Status diperbarui.');
    }

    public function destroy(Application $application)
    {
        Storage::disk('public')->delete($application->photo_path);
        $application->delete();

        return redirect('/admin/applications')->with('status', 'Pendaftaran dihapus.');
    }
}
