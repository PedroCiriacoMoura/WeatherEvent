<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_tokens(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Maria Silva',
            'email' => 'maria@weatherevent.com',
            'password' => 'senha-secreta',
            'password_confirmation' => 'senha-secreta',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'created_at'],
                'access_token',
                'refresh_token',
                'token_type',
                'expires_in',
            ])
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', 'maria@weatherevent.com');

        $this->assertDatabaseHas('users', ['email' => 'maria@weatherevent.com']);
        $this->assertDatabaseCount('personal_access_tokens', 2); 
    }

    public function test_register_validates_input(): void
    {
        $this->postJson('/api/register', [
            'name' => '',
            'email' => 'invalido',
            'password' => '123',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'maria@weatherevent.com']);

        $this->postJson('/api/register', [
            'name' => 'Outra Maria',
            'email' => 'maria@weatherevent.com',
            'password' => 'senha-secreta',
            'password_confirmation' => 'senha-secreta',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'maria@weatherevent.com',
            'password' => 'senha-secreta',
        ]);

        $this->postJson('/api/login', [
            'email' => 'maria@weatherevent.com',
            'password' => 'senha-secreta',
        ])->assertOk()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'created_at'],
                'access_token',
                'refresh_token',
                'token_type',
                'expires_in',
            ])
            ->assertJsonPath('user.id', $user->id);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'maria@weatherevent.com',
            'password' => 'senha-secreta',
        ]);

        $this->postJson('/api/login', [
            'email' => 'maria@weatherevent.com',
            'password' => 'senha-errada',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_user_can_access_me(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, [AuthService::ACCESS_ABILITY]);

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_guest_cannot_access_me(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }

    public function test_logout_revokes_the_session_tokens(): void
    {
        ['access_token' => $access] = $this->authenticate();
        $this->assertDatabaseCount('personal_access_tokens', 2);

        $this->withHeader('Authorization', 'Bearer '.$access)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logout realizado com sucesso.');

        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->forgetGuards();
        $this->withHeader('Authorization', 'Bearer '.$access)
            ->getJson('/api/me')
            ->assertUnauthorized();
    }

    public function test_refresh_token_issues_new_pair_and_rotates(): void
    {
        ['refresh_token' => $refresh] = $this->authenticate();

        $this->forgetGuards();
        $response = $this->withHeader('Authorization', 'Bearer '.$refresh)
            ->postJson('/api/refresh')
            ->assertOk()
            ->assertJsonStructure(['user', 'access_token', 'refresh_token', 'token_type', 'expires_in']);

        $newAccess = $response->json('access_token');
        $newRefresh = $response->json('refresh_token');
        $this->assertNotSame($refresh, $newRefresh);

        $this->forgetGuards();
        $this->withHeader('Authorization', 'Bearer '.$newAccess)
            ->getJson('/api/me')->assertOk();

        $this->forgetGuards();
        $this->withHeader('Authorization', 'Bearer '.$refresh)
            ->postJson('/api/refresh')->assertUnauthorized();

        $this->assertDatabaseCount('personal_access_tokens', 2);
    }

    public function test_access_token_cannot_be_used_to_refresh(): void
    {
        ['access_token' => $access] = $this->authenticate();

        $this->forgetGuards();
        $this->withHeader('Authorization', 'Bearer '.$access)
            ->postJson('/api/refresh')
            ->assertForbidden(); 
    }

    public function test_refresh_token_cannot_access_protected_route(): void
    {
        ['refresh_token' => $refresh] = $this->authenticate();

        $this->forgetGuards();
        $this->withHeader('Authorization', 'Bearer '.$refresh)
            ->getJson('/api/me')
            ->assertForbidden(); 
    }

    public function test_expired_access_token_is_rejected_but_refresh_still_works(): void
    {
        ['access_token' => $access, 'refresh_token' => $refresh] = $this->authenticate();

        $this->travel(61)->minutes();

        $this->forgetGuards();
        $this->withHeader('Authorization', 'Bearer '.$access)
            ->getJson('/api/me')
            ->assertUnauthorized();

        $this->forgetGuards();
        $this->withHeader('Authorization', 'Bearer '.$refresh)
            ->postJson('/api/refresh')
            ->assertOk();
    }

    private function authenticate(): array
    {
        $user = User::factory()->create(['email' => 'maria@weatherevent.com']);

        $tokens = app(AuthService::class)->login([
            'email' => $user->email,
            'password' => 'password', 
        ]);

        return [
            'user' => $user,
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
        ];
    }

    private function forgetGuards(): void
    {
        $this->app['auth']->forgetGuards();
    }
}
