<?php

namespace Akseonov\Php2\UnitTests\Actions\Users;

use Akseonov\Php2\Actions\Users\FindUserByUuid;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\SuccessfulResponse;
use Akseonov\Php2\Person\Name;
use JsonException;
use PHPUnit\Framework\TestCase;

class FindUserByUuidActionTest extends TestCase
{
    private function usersRepository(array $users): UsersRepositoryInterface
    {
        return new class($users) implements UsersRepositoryInterface
        {
            public function __construct(
                private array $users
            )
            {
            }

            public function save(User $user): void
            {
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
        };
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfNoUuidProvided(): void
    {
        $request = new Request([], [], "");

        $usersRepository = $this->usersRepository([]);

        $action = new FindUserByUuid($usersRepository);

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such query param in the request: uuid"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfUserNotFound(): void
    {
        $request = new Request([
            'uuid' => 'a3e78b09-23ae-44fd-9939-865f688894f5'
        ], [], "");

        $usersRepository = $this->usersRepository([]);

        $action = new FindUserByUuid($usersRepository);

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Not found"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfUuidNotValid(): void
    {
        $request = new Request([
            'uuid' => 'a3e78b099-865f688894f5'
        ], [], "");

        $usersRepository = $this->usersRepository([]);

        $action = new FindUserByUuid($usersRepository);

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Malformed UUID: a3e78b099-865f688894f5"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsSuccessfulResponse(): void
    {
        $request = new Request([
            'uuid' => '10373537-0805-4d7a-830e-22b481b4859c'
        ], [], '');

        $usersRepository = $this->usersRepository([
            new User(
                new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
                'ivan',
                new Name('Ivan', 'Nikitin')
            ),
        ]);

        $action = new FindUserByUuid($usersRepository);

        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $this->expectOutputString('{"success":true,"data":{"uuid":"10373537-0805-4d7a-830e-22b481b4859c","username":"ivan","first_name":"Ivan","last_name":"Nikitin"}}');

        $response->send();
    }
}