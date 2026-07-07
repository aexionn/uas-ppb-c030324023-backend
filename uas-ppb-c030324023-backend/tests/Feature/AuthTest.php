<?php

namespace Tests\Feature;

use App\Models\Account;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_can_register_via_api_and_receives_token(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'nisn' => '1234567890',
            'username' => 'budi',
            'email' => 'budi@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)->assertJsonStructure(['message', 'token']);
        $this->assertDatabaseHas('accounts', ['nisn' => '1234567890', 'role' => 'candidate']);
    }

    public function test_register_rejects_duplicate_nisn_username_email(): void
    {
        Account::create([
            'role' => 'candidate',
            'nisn' => '1234567890',
            'username' => 'budi',
            'email' => 'budi@test.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/register', [
            'nisn' => '1234567890',
            'username' => 'budi',
            'email' => 'budi@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nisn', 'username', 'email']);
    }

    public function test_register_rejects_nisn_not_10_digits(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'nisn' => '123',
            'username' => 'budi',
            'email' => 'budi@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['nisn']);
    }

    public function test_candidate_can_login_and_receives_token_with_sub_and_role(): void
    {
        $account = Account::create([
            'role' => 'candidate',
            'nisn' => '1234567890',
            'username' => 'budi',
            'email' => 'budi@test.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'nisn' => '1234567890',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)->assertJsonStructure(['message', 'token']);

        $payload = JWT::decode(
            $response->json('token'),
            new \Firebase\JWT\Key(config('jwt.secret'), 'HS256')
        );

        $this->assertEquals($account->id, $payload->sub);
        $this->assertEquals('candidate', $payload->role);
    }

    public function test_login_rejects_bad_credentials(): void
    {
        Account::create([
            'role' => 'candidate',
            'nisn' => '1234567890',
            'username' => 'budi',
            'email' => 'budi@test.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'nisn' => '1234567890',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_protected_route_rejects_missing_token(): void
    {
        $this->getJson('/api/v1/me')->assertStatus(401);
    }

    public function test_protected_route_rejects_invalid_token(): void
    {
        $this->getJson('/api/v1/me', [
            'Authorization' => 'Bearer not-a-real-token',
        ])->assertStatus(401);
    }

    public function test_protected_route_rejects_expired_token(): void
    {
        $account = Account::create([
            'role' => 'candidate',
            'nisn' => '1234567890',
            'username' => 'budi',
            'email' => 'budi@test.com',
            'password' => 'password123',
        ]);

        $expired = JWT::encode([
            'sub' => $account->id,
            'role' => 'candidate',
            'iat' => time() - 100000,
            'exp' => time() - 1,
        ], config('jwt.secret'), 'HS256');

        $this->getJson('/api/v1/me', [
            'Authorization' => "Bearer $expired",
        ])->assertStatus(401);
    }

    public function test_protected_route_accepts_valid_token(): void
    {
        $account = Account::create([
            'role' => 'candidate',
            'nisn' => '1234567890',
            'username' => 'budi',
            'email' => 'budi@test.com',
            'password' => 'password123',
        ]);

        $token = $this->postJson('/api/v1/login', [
            'nisn' => '1234567890',
            'password' => 'password123',
        ])->json('token');

        $this->getJson('/api/v1/me', [
            'Authorization' => "Bearer $token",
        ])->assertStatus(200)->assertJson(['nisn' => $account->nisn, 'username' => $account->username]);
    }

    public function test_role_middleware_blocks_candidate_from_admin_route(): void
    {
        Account::create([
            'role' => 'candidate',
            'nisn' => '1234567890',
            'username' => 'budi',
            'email' => 'budi@test.com',
            'password' => 'password123',
        ]);

        $token = $this->postJson('/api/v1/login', [
            'nisn' => '1234567890',
            'password' => 'password123',
        ])->json('token');

        $this->getJson('/api/v1/admin-probe', [
            'Authorization' => "Bearer $token",
        ])->assertStatus(403);
    }

    public function test_admin_login_via_web_with_username(): void
    {
        Account::create([
            'role' => 'admin',
            'nisn' => null,
            'username' => 'admin',
            'email' => 'admin@campus.test',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'identifier' => 'admin',
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
    }
}
