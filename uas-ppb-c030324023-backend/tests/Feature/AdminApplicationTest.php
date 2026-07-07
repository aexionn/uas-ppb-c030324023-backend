<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Application;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminApplicationTest extends TestCase
{
    use RefreshDatabase;

    private function candidate(string $nisn = '1234567890'): Account
    {
        return Account::create([
            'role' => 'candidate',
            'nisn' => $nisn,
            'username' => 'budi'.$nisn,
            'email' => 'budi'.$nisn.'@test.com',
            'password' => 'password123',
        ]);
    }

    private function admin(): Account
    {
        return Account::create([
            'role' => 'admin',
            'nisn' => null,
            'username' => 'admin',
            'email' => 'admin@campus.test',
            'password' => 'password',
        ]);
    }

    private function applicationFor(Account $candidate): Application
    {
        return Application::create([
            'account_id' => $candidate->id,
            'program_id' => Program::create(['name' => 'Teknik Informatika '.$candidate->id])->id,
            'full_name' => 'Budi Santoso',
            'birth_place' => 'Jakarta',
            'birth_date' => '2008-01-01',
            'gender' => 'L',
            'address' => 'Jl. Merdeka No. 1',
            'phone' => '081234567890',
            'school_origin' => 'SMA 1',
            'father_name' => 'Pak Budi',
            'father_job' => 'Wiraswasta',
            'mother_name' => 'Bu Budi',
            'mother_job' => 'Ibu Rumah Tangga',
            'parents_income' => '1-3jt',
            'photo_path' => 'photos/existing.jpg',
            'status' => 'submitted',
            'last_submitted_at' => now(),
        ]);
    }

    public function test_admin_verdict_locks_application_for_candidate(): void
    {
        $candidate = $this->candidate();
        $application = $this->applicationFor($candidate);

        $this->actingAs($this->admin())
            ->post("/admin/applications/{$application->id}/verdict", ['status' => 'accepted'])
            ->assertRedirect("/admin/applications/{$application->id}");

        $this->assertSame('accepted', $application->refresh()->status);

        $token = $this->postJson('/api/v1/login', ['nisn' => $candidate->nisn, 'password' => 'password123'])->json('token');
        $this->getJson('/api/v1/application', ['Authorization' => "Bearer $token"])
            ->assertJson(['status' => 'accepted', 'locked' => true]);
    }

    public function test_admin_edit_bypasses_candidate_lock(): void
    {
        Storage::fake('public');
        $candidate = $this->candidate();
        $application = $this->applicationFor($candidate);
        $application->update(['status' => 'accepted']);

        $this->assertTrue($application->refresh()->isLocked());

        $newProgram = Program::create(['name' => 'Sistem Informasi']);

        $this->actingAs($this->admin())
            ->put("/admin/applications/{$application->id}", [
                'program_id' => $newProgram->id,
                'full_name' => 'Budi Santoso Diubah Admin',
                'birth_place' => 'Jakarta',
                'birth_date' => '2008-01-01',
                'gender' => 'L',
                'address' => 'Jl. Merdeka No. 1',
                'phone' => '081234567890',
                'school_origin' => 'SMA 1',
                'father_name' => 'Pak Budi',
                'father_job' => 'Wiraswasta',
                'mother_name' => 'Bu Budi',
                'mother_job' => 'Ibu Rumah Tangga',
                'parents_income' => '1-3jt',
            ])
            ->assertRedirect("/admin/applications/{$application->id}");

        $this->assertSame('Budi Santoso Diubah Admin', $application->refresh()->full_name);
        $this->assertSame('accepted', $application->status);
    }

    public function test_delete_removes_application_and_photo_then_allows_resubmit(): void
    {
        Storage::fake('public');
        $candidate = $this->candidate();
        $photoPath = UploadedFile::fake()->image('foto.jpg')->store('photos', 'public');
        $application = $this->applicationFor($candidate);
        $application->update(['photo_path' => $photoPath]);

        $this->actingAs($this->admin())
            ->delete("/admin/applications/{$application->id}")
            ->assertRedirect('/admin/applications');

        $this->assertDatabaseMissing('applications', ['id' => $application->id]);
        Storage::disk('public')->assertMissing($photoPath);
        $this->assertDatabaseHas('accounts', ['id' => $candidate->id]);

        $token = $this->postJson('/api/v1/login', ['nisn' => $candidate->nisn, 'password' => 'password123'])->json('token');
        $this->postJson('/api/v1/application', [
            'program_id' => Program::create(['name' => 'Teknik Elektro'])->id,
            'full_name' => 'Budi Santoso',
            'birth_place' => 'Jakarta',
            'birth_date' => '2008-01-01',
            'gender' => 'L',
            'address' => 'Jl. Merdeka No. 1',
            'phone' => '081234567890',
            'school_origin' => 'SMA 1',
            'father_name' => 'Pak Budi',
            'father_job' => 'Wiraswasta',
            'mother_name' => 'Bu Budi',
            'mother_job' => 'Ibu Rumah Tangga',
            'parents_income' => '1-3jt',
            'photo' => UploadedFile::fake()->image('foto2.jpg'),
        ], ['Authorization' => "Bearer $token"])
            ->assertStatus(201)
            ->assertJson(['edits_remaining' => 3]);
    }

    public function test_candidate_cannot_access_admin_application_routes(): void
    {
        $candidate = $this->candidate();
        $application = $this->applicationFor($candidate);

        $this->actingAs($candidate)
            ->get("/admin/applications/{$application->id}")
            ->assertStatus(403);

        $this->actingAs($candidate)
            ->post("/admin/applications/{$application->id}/verdict", ['status' => 'accepted'])
            ->assertStatus(403);
    }

    public function test_admin_can_search_and_filter_application_list(): void
    {
        $applicationA = $this->applicationFor($this->candidate('1111111111'));
        $applicationB = $this->applicationFor($this->candidate('2222222222'));
        $applicationB->update(['status' => 'accepted']);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/admin/applications?search='.$applicationA->account->nisn)
            ->assertStatus(200)
            ->assertSee($applicationA->full_name);

        $this->actingAs($admin)
            ->get('/admin/applications?status=accepted')
            ->assertStatus(200)
            ->assertSee($applicationB->full_name);
    }
}
