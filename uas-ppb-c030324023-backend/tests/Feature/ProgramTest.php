<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramTest extends TestCase
{
    use RefreshDatabase;

    private function candidate(): Account
    {
        return Account::create([
            'role' => 'candidate',
            'nisn' => '1234567890',
            'username' => 'budi',
            'email' => 'budi@test.com',
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

    private function tokenFor(Account $account): string
    {
        return $this->postJson('/api/v1/login', [
            'nisn' => $account->nisn,
            'password' => 'password123',
        ])->json('token');
    }

    public function test_api_programs_returns_list_for_authenticated_candidate(): void
    {
        Program::create(['name' => 'Teknik Informatika']);
        Program::create(['name' => 'Akuntansi']);

        $token = $this->tokenFor($this->candidate());

        $this->getJson('/api/v1/programs', ['Authorization' => "Bearer $token"])
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonStructure([['id', 'name']]);
    }

    public function test_api_programs_requires_authentication(): void
    {
        $this->getJson('/api/v1/programs')->assertStatus(401);
    }

    public function test_admin_can_create_program(): void
    {
        $this->actingAs($this->admin())
            ->post('/programs', ['name' => 'Sistem Informasi'])
            ->assertRedirect('/programs');

        $this->assertDatabaseHas('programs', ['name' => 'Sistem Informasi']);
    }

    public function test_admin_can_update_program(): void
    {
        $program = Program::create(['name' => 'Lama']);

        $this->actingAs($this->admin())
            ->put('/programs/'.$program->id, ['name' => 'Baru'])
            ->assertRedirect('/programs');

        $this->assertDatabaseHas('programs', ['id' => $program->id, 'name' => 'Baru']);
    }

    public function test_admin_can_delete_program(): void
    {
        $program = Program::create(['name' => 'Hapus Saya']);

        $this->actingAs($this->admin())
            ->delete('/programs/'.$program->id)
            ->assertRedirect('/programs');

        $this->assertDatabaseMissing('programs', ['id' => $program->id]);
    }

    public function test_candidate_cannot_reach_program_crud(): void
    {
        $candidate = $this->candidate();

        $this->actingAs($candidate)->get('/programs')->assertStatus(403);
        $this->actingAs($candidate)->post('/programs', ['name' => 'X'])->assertStatus(403);
        $this->assertDatabaseMissing('programs', ['name' => 'X']);
    }

    public function test_guest_is_redirected_from_program_crud(): void
    {
        $this->get('/programs')->assertRedirect('/login');
    }
}
