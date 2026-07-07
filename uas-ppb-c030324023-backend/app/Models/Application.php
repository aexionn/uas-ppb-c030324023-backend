<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'account_id', 'program_id', 'full_name', 'birth_place', 'birth_date', 'gender',
    'address', 'phone', 'school_origin', 'father_name', 'father_job', 'mother_name',
    'mother_job', 'parents_income', 'photo_path', 'status', 'edits_used', 'last_submitted_at',
])]
class Application extends Model
{
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'last_submitted_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function editableUntil(): \Carbon\Carbon
    {
        return $this->last_submitted_at->copy()->addMinutes(10);
    }

    public function editsRemaining(): int
    {
        return max(0, 3 - $this->edits_used);
    }

    // ponytail: verdict (status != submitted) takes priority over budget/time,
    // matching "admin verdict locks regardless of time/edit count remaining".
    public function lockReason(): ?string
    {
        if ($this->status !== 'submitted') {
            return 'APPLICATION_LOCKED';
        }
        if ($this->editsRemaining() === 0) {
            return 'EDITS_EXHAUSTED';
        }
        if (now()->greaterThan($this->editableUntil())) {
            return 'APPLICATION_LOCKED';
        }

        return null;
    }

    public function isLocked(): bool
    {
        return $this->lockReason() !== null;
    }
}
