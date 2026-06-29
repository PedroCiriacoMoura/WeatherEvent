<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public const ACCESS_ABILITY = 'access-api';
    public const REFRESH_ABILITY = 'refresh-api';

    /**
     * Registra um novo usuário e emite o par de tokens (access + refresh).
     *
     * @param  array{name: string, email: string, password: string}  $data
     * @return array{user: User, access_token: string, refresh_token: string, expires_in: int}
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // cast "hashed" no model aplica o hash
        ]);

        return $this->issueTokens($user);
    }

    /**
     * Valida as credenciais e emite o par de tokens (access + refresh).
     *
     * @param  array{email: string, password: string}  $credentials
     * @return array{user: User, access_token: string, refresh_token: string, expires_in: int}
     *
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        return $this->issueTokens($user);
    }

    /**
     * Renova a sessão: revoga o par atual (rotação) e emite um novo par.
     * Deve ser chamado autenticado com o refresh token.
     *
     * @return array{user: User, access_token: string, refresh_token: string, expires_in: int}
     */
    public function refresh(User $user): array
    {
        $this->revokeCurrentSession($user);

        return $this->issueTokens($user);
    }

    /**
     * Revoga o par de tokens da sessão usada na requisição atual (logout).
     */
    public function logout(User $user): void
    {
        $this->revokeCurrentSession($user);
    }

    /**
     * Emite um par access/refresh vinculado por um grupo de sessão.
     *
     * @return array{user: User, access_token: string, refresh_token: string, expires_in: int}
     */
    private function issueTokens(User $user): array
    {
        $group = (string) Str::uuid();
        $accessTtl = (int) config('sanctum.access_token_expiration');
        $refreshTtl = (int) config('sanctum.refresh_token_expiration');

        $accessToken = $user->createToken(
            $group.':access',
            [self::ACCESS_ABILITY],
            now()->addMinutes($accessTtl)
        )->plainTextToken;

        $refreshToken = $user->createToken(
            $group.':refresh',
            [self::REFRESH_ABILITY],
            now()->addMinutes($refreshTtl)
        )->plainTextToken;

        return [
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => $accessTtl * 60, // segundos
        ];
    }

    /**
     * Remove todos os tokens (access + refresh) do grupo de sessão atual.
     */
    private function revokeCurrentSession(User $user): void
    {
        $group = Str::before($user->currentAccessToken()->name, ':');

        $user->tokens()->where('name', 'like', $group.':%')->delete();
    }
}
