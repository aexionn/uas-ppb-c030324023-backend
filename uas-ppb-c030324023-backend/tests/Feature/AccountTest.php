<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    private function candidateToken(): array
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

        return [$account, $token];
    }

    public function test_get_me_returns_nisn_username_email(): void
    {
        [$account, $token] = $this->candidateToken();

        $this->getJson('/api/v1/me', ['Authorization' => "Bearer $token"])
            ->assertStatus(200)
            ->assertJson([
                'nisn' => $account->nisn,
                'username' => $account->username,
                'email' => $account->email,
            ]);
    }

    public function test_put_me_password_changes_password_and_new_password_works_on_login(): void
    {
        [, $token] = $this->candidateToken();

        $this->putJson('/api/v1/me/password', [
            'current_password' => 'password123',
            'new_password' => 'newpassword456',
        ], ['Authorization' => "Bearer $token"])->assertStatus(200);

        $this->postJson('/api/v1/login', [
            'nisn' => '1234567890',
            'password' => 'newpassword456',
        ])->assertStatus(200);

        $this->postJson('/api/v1/login', [
            'nisn' => '1234567890',
            'password' => 'password123',
        ])->assertStatus(401);
    }

    public function test_put_me_password_rejects_wrong_current_password(): void
    {
        [, $token] = $this->candidateToken();

        $this->putJson('/api/v1/me/password', [
            'current_password' => 'wrong-password',
            'new_password' => 'newpassword456',
        ], ['Authorization' => "Bearer $token"])
            ->assertStatus(422)
            ->assertJson(['code' => 'INVALID_CURRENT_PASSWORD']);
    }

    public function test_web_account_page_shows_nisn_username_email_for_candidate(): void
    {
        $account = Account::create([
            'role' => 'candidate',
            'nisn' => '1234567890',
            'username' => 'budi',
            'email' => 'budi@test.com',
            'password' => 'password123',
        ]);

        $this->actingAs($account)
            ->get('/account')
            ->assertStatus(200)
            ->assertSee($account->nisn)
            ->assertSee($account->username)
            ->assertSee($account->email);
    }

    public function test_web_account_page_shows_username_email_for_admin_without_nisn(): void
    {
        $admin = Account::create([
            'role' => 'admin',
            'nisn' => null,
            'username' => 'admin',
            'email' => 'admin@campus.test',
            'password' => 'password',
        ]);

        $this->actingAs($admin)
            ->get('/account')
            ->assertStatus(200)
            ->assertSee($admin->username)
            ->assertSee($admin->email);
    }

    public function test_web_change_password_works_for_candidate(): void
    {
        $account = Account::create([
            'role' => 'candidate',
            'nisn' => '1234567890',
            'username' => 'budi',
            'email' => 'budi@test.com',
            'password' => 'password123',
        ]);

        $this->actingAs($account)->post('/account/password', [
            'current_password' => 'password123',
            'new_password' => 'newpassword456',
        ])->assertRedirect();

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check(
            'newpassword456',
            $account->fresh()->password
        ));
    }

    public function test_web_change_password_works_for_admin(): void
    {
        $admin = Account::create([
            'role' => 'admin',
            'nisn' => null,
            'username' => 'admin',
            'email' => 'admin@campus.test',
            'password' => 'password',
        ]);

        $this->actingAs($admin)->post('/account/password', [
            'current_password' => 'password',
            'new_password' => 'newpassword456',
        ])->assertRedirect();

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check(
            'newpassword456',
            $admin->fresh()->password
        ));
    }

    public function test_account_page_requires_authentication(): void
    {
        $this->get('/account')->assertRedirect('/login');
    }
}
