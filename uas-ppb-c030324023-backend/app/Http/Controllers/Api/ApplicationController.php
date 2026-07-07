<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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

    public function store(Request $request)
    {
        if (Application::where('account_id', $request->user()->id)->exists()) {
            return response()->json([
                'message' => 'Anda sudah memiliki pendaftaran.',
                'errors' => [],
                'code' => 'APPLICATION_EXISTS',
            ], 422);
        }

        $validator = Validator::make($request->all(), self::RULES);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors(),
                'code' => 'VALIDATION_ERROR',
            ], 422);
        }

        $data = $validator->validated();
        $data['photo_path'] = $request->file('photo')->store('photos', 'public');
        $data['account_id'] = $request->user()->id;
        $data['last_submitted_at'] = now();
        $data['status'] = 'submitted';

        $application = Application::create($data);

        return response()->json($this->present($application), 201);
    }

    public function show(Request $request)
    {
        $application = Application::where('account_id', $request->user()->id)->first();

        if (! $application) {
            return response()->json([
                'message' => 'Anda belum memiliki pendaftaran.',
                'errors' => [],
                'code' => 'APPLICATION_NOT_FOUND',
            ], 404);
        }

        return response()->json($this->present($application));
    }

    public function update(Request $request)
    {
        $application = Application::where('account_id', $request->user()->id)->first();

        if (! $application) {
            return response()->json([
                'message' => 'Anda belum memiliki pendaftaran.',
                'errors' => [],
                'code' => 'APPLICATION_NOT_FOUND',
            ], 404);
        }

        if ($reason = $application->lockReason()) {
            return response()->json([
                'message' => $reason === 'EDITS_EXHAUSTED'
                    ? 'Batas perubahan sudah tercapai.'
                    : 'Pendaftaran terkunci dan tidak dapat diubah.',
                'errors' => [],
                'code' => $reason,
            ], 422);
        }

        $rules = self::RULES;
        $rules['photo'] = ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors(),
                'code' => 'VALIDATION_ERROR',
            ], 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('photo')) {
            Storage::disk('public')->delete($application->photo_path);
            $data['photo_path'] = $request->file('photo')->store('photos', 'public');
        }

        $data['edits_used'] = $application->edits_used + 1;
        $data['last_submitted_at'] = now();

        $application->update($data);

        return response()->json($this->present($application->refresh()));
    }

    private function present(Application $application): array
    {
        return [
            ...$application->toArray(),
            'photo_url' => asset('storage/'.$application->photo_path),
            'edits_remaining' => $application->editsRemaining(),
            'locked' => $application->isLocked(),
            'editable_until' => $application->editableUntil(),
        ];
    }
}
