<?php

namespace App\Services;

use App\Contracts\Repositories\SessionRepository;
use App\Models\Users\User;
use Laravel\Passport\Token;

class AuthService
{
    private $sessions;

    public function __construct(SessionRepository $sessions)
    {
        $this->sessions = $sessions;
    }

    public function requestAccessToken(string $username, string $password, string $scope = '')
    {
        $http = new \GuzzleHttp\Client();

        $response = $http->post(route('passport.token'), [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => config('passport.password_client_id'),
                'client_secret' => config('passport.password_client_secret'),
                'username' => $username,
                'password' => $password,
                'scope' => $scope,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    public function revokeSessionTokenByIp(string $ipAddress)
    {
        $this->sessions
             ->whereIpAddress($ipAddress)
             ->each(function ($session) {
                 $this->revokeToken($session->token);
             });
    }

    public function revokeUserTokens(User $user)
    {
        $this->sessions
             ->whereUserId($user->getKey())
             ->each(function ($session) {
                $this->revokeToken($session->token);
             });
    }

    /**
     * @param Token $token
     * @return bool
     * @throws \Exception
     */
    public function revokeToken(?Token $token): bool
    {
        if ($token instanceof Token) {
            return $token->revoke();
        }

        throw new \Exception("Can't revoke invalid token");
    }
}
