<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Application;
use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

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

    private function tokenFor(Account $account): string
    {
        return $this->postJson('/api/v1/login', [
            'nisn' => $account->nisn,
            'password' => 'password123',
        ])->json('token');
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'program_id' => $overrides['program_id'] ?? Program::create(['name' => 'Teknik Informatika'])->id,
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
            'photo' => UploadedFile::fake()->image('foto.jpg'),
        ], $overrides);
    }

    public function test_api_create_application_with_photo(): void
    {
        Storage::fake('public');
        $token = $this->tokenFor($this->candidate());

        $this->postJson('/api/v1/application', $this->payload(), ['Authorization' => "Bearer $token"])
            ->assertStatus(201)
            ->assertJsonStructure(['id', 'status', 'photo_url', 'edits_remaining', 'locked', 'editable_until']);

        $application = Application::first();
        Storage::disk('public')->assertExists($application->photo_path);
        $this->assertSame('submitted', $application->status);
        $this->assertSame(0, $application->edits_used);
    }

    public function test_api_create_application_without_photo_fails(): void
    {
        Storage::fake('public');
        $token = $this->tokenFor($this->candidate());
        $payload = $this->payload();
        unset($payload['photo']);

        $this->postJson('/api/v1/application', $payload, ['Authorization' => "Bearer $token"])
            ->assertStatus(422);
    }

    public function test_api_view_application_shape(): void
    {
        Storage::fake('public');
        $token = $this->tokenFor($this->candidate());
        $this->postJson('/api/v1/application', $this->payload(), ['Authorization' => "Bearer $token"]);

        $this->getJson('/api/v1/application', ['Authorization' => "Bearer $token"])
            ->assertStatus(200)
            ->assertJson(['status' => 'submitted', 'edits_remaining' => 3, 'locked' => false]);
    }

    public function test_api_candidate_cannot_view_another_candidates_application(): void
    {
        Storage::fake('public');
        $tokenA = $this->tokenFor($this->candidate('1111111111'));
        $this->postJson('/api/v1/application', $this->payload(), ['Authorization' => "Bearer $tokenA"]);

        $tokenB = $this->tokenFor($this->candidate('2222222222'));
        $this->getJson('/api/v1/application', ['Authorization' => "Bearer $tokenB"])
            ->assertStatus(404);
    }

    public function test_web_candidate_can_submit_and_view_application(): void
    {
        Storage::fake('public');
        $candidate = $this->candidate();

        $this->actingAs($candidate)
            ->post('/application', $this->payload())
            ->assertRedirect('/application');

        $this->assertDatabaseHas('applications', ['account_id' => $candidate->id]);

        $this->actingAs($candidate)
            ->get('/application')
            ->assertStatus(200)
            ->assertSee('submitted');
    }

    public function test_api_edit_within_window_succeeds_and_resets_window(): void
    {
        Storage::fake('public');
        Carbon::setTestNow('2026-07-04 10:00:00');
        $token = $this->tokenFor($this->candidate());
        $this->postJson('/api/v1/application', $this->payload(), ['Authorization' => "Bearer $token"]);

        Carbon::setTestNow('2026-07-04 10:05:00');
        $payload = $this->payload(['program_id' => Application::first()->program_id, 'full_name' => 'Budi Santoso Edited']);
        unset($payload['photo']);

        $this->post('/api/v1/application', [...$payload, '_method' => 'PUT'], ['Authorization' => "Bearer $token"])
            ->assertStatus(200)
            ->assertJson(['full_name' => 'Budi Santoso Edited', 'edits_remaining' => 2, 'locked' => false]);

        $application = Application::first();
        $this->assertSame(1, $application->edits_used);
        $this->assertTrue($application->last_submitted_at->equalTo(Carbon::parse('2026-07-04 10:05:00')));
    }

    public function test_api_fourth_edit_attempt_rejected_with_edits_exhausted(): void
    {
        Storage::fake('public');
        Carbon::setTestNow('2026-07-04 10:00:00');
        $token = $this->tokenFor($this->candidate());
        $this->postJson('/api/v1/application', $this->payload(), ['Authorization' => "Bearer $token"]);

        $payload = $this->payload(['program_id' => Application::first()->program_id]);
        unset($payload['photo']);

        for ($i = 0; $i < 3; $i++) {
            $this->post('/api/v1/application', [...$payload, '_method' => 'PUT'], ['Authorization' => "Bearer $token"])
                ->assertStatus(200);
        }

        $this->post('/api/v1/application', [...$payload, '_method' => 'PUT'], ['Authorization' => "Bearer $token"])
            ->assertStatus(422)
            ->assertJson(['code' => 'EDITS_EXHAUSTED']);
    }

    public function test_api_edit_after_deadline_rejected_with_application_locked(): void
    {
        Storage::fake('public');
        Carbon::setTestNow('2026-07-04 10:00:00');
        $token = $this->tokenFor($this->candidate());
        $this->postJson('/api/v1/application', $this->payload(), ['Authorization' => "Bearer $token"]);

        Carbon::setTestNow('2026-07-04 10:10:01');
        $payload = $this->payload(['program_id' => Application::first()->program_id]);
        unset($payload['photo']);

        $this->post('/api/v1/application', [...$payload, '_method' => 'PUT'], ['Authorization' => "Bearer $token"])
            ->assertStatus(422)
            ->assertJson(['code' => 'APPLICATION_LOCKED']);
    }

    public function test_api_edit_when_status_not_submitted_rejected_with_application_locked(): void
    {
        Storage::fake('public');
        Carbon::setTestNow('2026-07-04 10:00:00');
        $token = $this->tokenFor($this->candidate());
        $this->postJson('/api/v1/application', $this->payload(), ['Authorization' => "Bearer $token"]);
        Application::first()->update(['status' => 'accepted']);

        $payload = $this->payload(['program_id' => Application::first()->program_id]);
        unset($payload['photo']);

        $this->post('/api/v1/application', [...$payload, '_method' => 'PUT'], ['Authorization' => "Bearer $token"])
            ->assertStatus(422)
            ->assertJson(['code' => 'APPLICATION_LOCKED']);
    }

    public function test_api_view_application_lock_fields_agree_at_boundary(): void
    {
        Storage::fake('public');
        Carbon::setTestNow('2026-07-04 10:00:00');
        $token = $this->tokenFor($this->candidate());
        $this->postJson('/api/v1/application', $this->payload(), ['Authorization' => "Bearer $token"]);

        Carbon::setTestNow('2026-07-04 10:10:00');
        $this->getJson('/api/v1/application', ['Authorization' => "Bearer $token"])
            ->assertJson(['locked' => false]);

        Carbon::setTestNow('2026-07-04 10:10:01');
        $this->getJson('/api/v1/application', ['Authorization' => "Bearer $token"])
            ->assertJson(['locked' => true]);
    }

    public function test_web_candidate_can_edit_application(): void
    {
        Storage::fake('public');
        Carbon::setTestNow('2026-07-04 10:00:00');
        $candidate = $this->candidate();
        $this->actingAs($candidate)->post('/application', $this->payload());

        Carbon::setTestNow('2026-07-04 10:05:00');
        $payload = $this->payload(['program_id' => Application::first()->program_id, 'full_name' => 'Budi Santoso Edited']);
        unset($payload['photo']);

        $this->actingAs($candidate)
            ->put('/application', $payload)
            ->assertRedirect('/application');

        $this->assertDatabaseHas('applications', [
            'account_id' => $candidate->id,
            'full_name' => 'Budi Santoso Edited',
            'edits_used' => 1,
        ]);
    }
}
