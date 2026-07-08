<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;

class AdminApplicationController extends Controller
{
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

        return response()->json($query->orderByDesc('created_at')->get());
    }

    public function show(Application $application)
    {
        return response()->json($application->load(['account', 'program']));
    }

    public function verdict(Request $request, Application $application)
    {
        $data = $request->validate(['status' => ['required', 'in:accepted,rejected']]);
        $application->update($data);

        return response()->json([
            'message' => 'Status diperbarui.',
            'application' => $application->load(['account', 'program'])
        ]);
    }
}
