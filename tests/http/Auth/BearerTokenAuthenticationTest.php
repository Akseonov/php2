<?php

namespace Akseonov\Php2\UnitTests\http\Auth;

use Akseonov\Php2\Blog\AuthToken;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\AuthTokensRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\AuthException;
use Akseonov\Php2\Exceptions\AuthTokenNotFoundException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\Auth\BearerTokenAuthentication;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\Person\Name;
use DateTimeImmutable;
use Monolog\Test\TestCase;

class BearerTokenAuthenticationTest extends TestCase
{
    private function tokenAuthenticationUserReturn(AuthToken $authToken): AuthTokensRepositoryInterface
    {
        return new class($authToken) implements AuthTokensRepositoryInterface {
            public function __construct(
                private readonly AuthToken $authToken
            )
            {
            }

            public function save(AuthToken $authToken): void
            {
            }

            public function get(string $token): AuthToken
            {
                if ($this->authToken->getToken() === $token) {
                    return $this->authToken;
                }
                throw new AuthTokenNotFoundException('Not found');
            }
        };
    }

    private function usersRepository(array $users): UsersRepositoryInterface
    {
        return new class($users) implements UsersRepositoryInterface
        {
            private bool $called = false;

            public function __construct(
                private readonly array $users,
            )
            {
            }

            public function save(User $user): void
            {
                $this->called = true;
            }

            public function get(UUID $uuid): User
            {
                foreach ($this->users as $user) {
                    if ($user instanceof User && (string)$uuid === $user->getUuid()) {
                        return $user;
                    }
                }
                throw new UserNotFoundException('Not found');
            }

            public function getByUsername(string $username): User
            {

                throw new UserNotFoundException('Not found');
            }

            public function getCalled(): bool
            {
                return $this->called;
            }
        };
    }

    /**
     * @throws AuthException
     */
    public function testItAuthExceptionIfHeaderNotProvided() {
        $request = new Request([], [], '{}');

        $usersRepository = $this->usersRepository([]);
        $tokenAuthentication = $this->tokenAuthenticationUserReturn(
            new AuthToken(
                '4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52',
                new UUID('3a6ec3a6-f25b-438a-93d4-8392f620a702'),
                new DateTimeImmutable('2022-08-29T11:02:35+00:00')
            )
        );

        $bearerTokenAuth = new BearerTokenAuthentication(
            $tokenAuthentication,
            $usersRepository
        );

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Not such header in request: Authorization');

        $bearerTokenAuth->user($request);
    }

    public function testItAuthExceptionIfPrefixNotProvided() {
        $request = new Request([], [
            'HTTP_AUTHORIZATION' => '214'
        ], '{}');

        $usersRepository = $this->usersRepository([]);
        $tokenAuthentication = $this->tokenAuthenticationUserReturn(
            new AuthToken(
                '4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52',
                new UUID('3a6ec3a6-f25b-438a-93d4-8392f620a702'),
                new DateTimeImmutable('2022-08-29T11:02:35+00:00')
            )
        );

        $bearerTokenAuth = new BearerTokenAuthentication(
            $tokenAuthentication,
            $usersRepository
        );

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Malformed token: [214]');

        $bearerTokenAuth->user($request);
    }

    public function testItAuthExceptionIfBadToken() {
        $request = new Request([], [
            'HTTP_AUTHORIZATION' => 'Bearer 1234'
        ], '{}');

        $usersRepository = $this->usersRepository([]);
        $tokenAuthentication = $this->tokenAuthenticationUserReturn(
            new AuthToken(
                '4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52',
                new UUID('3a6ec3a6-f25b-438a-93d4-8392f620a702'),
                new DateTimeImmutable('2022-08-29T11:02:35+00:00')
            )
        );

        $bearerTokenAuth = new BearerTokenAuthentication(
            $tokenAuthentication,
            $usersRepository
        );

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Bad token: [1234]');

        $bearerTokenAuth->user($request);
    }

    public function testItAuthExceptionIfTokenExpired() {
        $request = new Request([], [
            'HTTP_AUTHORIZATION' => 'Bearer 4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52'
        ], '{}');

        $usersRepository = $this->usersRepository([]);
        $tokenAuthentication = $this->tokenAuthenticationUserReturn(
            new AuthToken(
                '4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52',
                new UUID('3a6ec3a6-f25b-438a-93d4-8392f620a702'),
                new DateTimeImmutable('2022-08-29T11:02:35+00:00')
            )
        );

        $bearerTokenAuth = new BearerTokenAuthentication(
            $tokenAuthentication,
            $usersRepository
        );

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Token expired: [4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52]');

        $bearerTokenAuth->user($request);
    }

    /**
     * @throws AuthException
     */
    public function testItReturnUser() {
        $request = new Request([], [
            'HTTP_AUTHORIZATION' => 'Bearer 4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52'
        ], '{}');

        $user = new User(
            new UUID('3a6ec3a6-f25b-438a-93d4-8392f620a702'),
            'ivan',
            '12345',
            new Name('first_name', 'last_name')
        );

        $usersRepository = $this->usersRepository([
            $user,
        ]);
        $tokenAuthentication = $this->tokenAuthenticationUserReturn(
            new AuthToken(
                '4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52',
                new UUID('3a6ec3a6-f25b-438a-93d4-8392f620a702'),
                new DateTimeImmutable('2035-11-25T11:02:35+00:00')
            )
        );

        $bearerTokenAuth = new BearerTokenAuthentication(
            $tokenAuthentication,
            $usersRepository
        );

        $result = $bearerTokenAuth->user($request);

        $this->assertEquals($user, $result);
    }
}