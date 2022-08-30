<?php

namespace Akseonov\Php2\UnitTests\http\Auth;

use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\AuthException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\Auth\JsonBodyUuidIdentification;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\Person\Name;
use Monolog\Test\TestCase;

class JsonBodyUuidIdentificationTest extends TestCase
{
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
                foreach ($this->users as $user) {
                    if ($user instanceof User && $username === $user->getUsername()) {
                        return $user;
                    }
                }
                throw new UserNotFoundException('Not found');
            }

            public function getCalled(): bool
            {
                return $this->called;
            }
        };
    }

    public function testItAuthExceptionIfUuidNotProvided() {
        $request = new Request([], [], '{}');

        $usersRepository = $this->usersRepository([]);

        $bearerTokenAuth = new JsonBodyUuidIdentification(
            $usersRepository
        );

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('No such Field: user_uuid');

        $bearerTokenAuth->user($request);
    }

    public function testItAuthExceptionIfUserNotFound() {
        $request = new Request([], [], '{"user_uuid":"3a6ec3a6-f25b-438a-93d4-8392f620a702"}');

        $usersRepository = $this->usersRepository([]);

        $bearerTokenAuth = new JsonBodyUuidIdentification(
            $usersRepository
        );

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Not found');

        $bearerTokenAuth->user($request);
    }

    public function testItReturn() {
        $request = new Request([], [], '{"user_uuid":"3a6ec3a6-f25b-438a-93d4-8392f620a702"}');

        $usersRepository = $this->usersRepository([
            new User(
                new UUID('3a6ec3a6-f25b-438a-93d4-8392f620a702'),
                'ivan',
                '12345',
                new Name('first_name', 'last_name')
            )
        ]);

        $bearerTokenAuth = new JsonBodyUuidIdentification(
            $usersRepository
        );

        $user = $bearerTokenAuth->user($request);

        $this->assertEquals(new User(
            new UUID('3a6ec3a6-f25b-438a-93d4-8392f620a702'),
            'ivan',
            '12345',
            new Name('first_name', 'last_name')
        ), $user);
    }
}