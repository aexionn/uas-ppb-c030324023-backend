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

        $applications = $query->orderByDesc('created_at')->get()->map(function ($app) {
            return $this->present($app);
        });

        return response()->json($applications);
    }

    public function show(Application $application)
    {
        $application->load(['account', 'program']);
        return response()->json($this->present($application));
    }

    public function verdict(Request $request, Application $application)
    {
        $data = $request->validate(['status' => ['required', 'in:accepted,rejected']]);
        $application->update($data);

        $application->load(['account', 'program']);

        return response()->json([
            'message' => 'Status diperbarui.',
            'application' => $this->present($application)
        ]);
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
