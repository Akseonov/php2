<?php

namespace Akseonov\Php2\http\Auth;

use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\AuthTokensRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Exceptions\AuthException;
use Akseonov\Php2\Exceptions\AuthTokenNotFoundException;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\http\Auth\Interfaces\TokenAuthenticationInterface;
use Akseonov\Php2\http\Request;
use DateTimeImmutable;

class BearerTokenAuthentication implements TokenAuthenticationInterface
{
    private const HEADER_PREFIX = 'Bearer ';

    public function __construct(
        private readonly AuthTokensRepositoryInterface $authTokensRepository,
        private readonly UsersRepositoryInterface      $usersRepository,
    ) {
    }

    /**
     * @throws AuthException
     */
    public function user(Request $request): User
    {
        try {
            $header = $request->header('Authorization');
        } catch (HttpException $exception) {
            throw new AuthException($exception->getMessage());
        }

        if (!str_starts_with($header, self::HEADER_PREFIX)) {
            throw new AuthException("Malformed token: [$header]");
        }

        $token = mb_substr($header, strlen(self::HEADER_PREFIX));

        try {
            $authToken = $this->authTokensRepository->get($token);
        } catch (AuthTokenNotFoundException) {
            throw new AuthException("Bad token: [$token]");
        }

        if ($authToken->getExpiresOn() <= new DateTimeImmutable()) {
            throw new AuthException("Token expired: [$token]");
        }

        $userUuid = $authToken->getUserUuid();

        return $this->usersRepository->get($userUuid);
    }
}