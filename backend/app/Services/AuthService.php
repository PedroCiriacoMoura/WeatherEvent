<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public const TYPE_ACCESS = 'access';
    public const TYPE_REFRESH = 'refresh';

    private const REVOKED_SESSION_PREFIX = 'jwt:revoked:';

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
        $this->revokeCurrentSession();

        return $this->issueTokens($user);
    }

    /**
     * Revoga o par de tokens da sessão usada na requisição atual (logout).
     */
    public function logout(User $user): void
    {
        $this->revokeCurrentSession();
    }

    /**
     * Indica se a sessão (claim "sid") foi revogada via logout ou rotação.
     */
    public static function sessionIsRevoked(string $sid): bool
    {
        return Cache::has(self::REVOKED_SESSION_PREFIX.$sid);
    }

    /**
     * Emite um par access/refresh de JWTs vinculados por um "sid" de sessão.
     *
     * @return array{user: User, access_token: string, refresh_token: string, expires_in: int}
     */
    private function issueTokens(User $user): array
    {
        $sid = (string) Str::uuid();
        $accessTtl = (int) config('jwt.access_token_ttl');
        $refreshTtl = (int) config('jwt.refresh_token_ttl');

        $accessToken = $this->makeToken($user, self::TYPE_ACCESS, $sid, $accessTtl);
        $refreshToken = $this->makeToken($user, self::TYPE_REFRESH, $sid, $refreshTtl);

        return [
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => $accessTtl * 60, // segundos
        ];
    }

    /**
     * Gera um JWT com os claims "type" e "sid" e o TTL informado (em minutos).
     */
    private function makeToken(User $user, string $type, string $sid, int $ttlMinutes): string
    {
        JWTAuth::factory()->setTTL($ttlMinutes);

        return JWTAuth::customClaims([
            'type' => $type,
            'sid' => $sid,
        ])->fromUser($user);
    }

    /**
     * Adiciona o "sid" do token da requisição atual à blacklist de sessão,
     * invalidando o par (access + refresh) até o fim do TTL do refresh.
     */
    private function revokeCurrentSession(): void
    {
        $sid = (string) JWTAuth::setRequest(request())->parseToken()->getPayload()->get('sid');
        $refreshTtl = (int) config('jwt.refresh_token_ttl');

        Cache::put(
            self::REVOKED_SESSION_PREFIX.$sid,
            true,
            now()->addMinutes($refreshTtl)
        );
    }
}
