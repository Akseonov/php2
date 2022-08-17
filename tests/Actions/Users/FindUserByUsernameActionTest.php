<?php

namespace Akseonov\Php2\UnitTests\Actions\Users;

use Akseonov\Php2\Actions\Users\FindUserByUsername;
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

class FindUserByUsernameActionTest extends TestCase
{
    private function usersRepository(array $users): UsersRepositoryInterface
    {
        return new class($users) implements UsersRepositoryInterface {
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
                throw new UserNotFoundException('Not found');
            }

            /**
             * @return array
             */
            public function getUsers(): array
            {
                return $this->users;
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
        };
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfNoUsernameProvided(): void
    {
        $request = new Request([], [], "");

        $usersRepository = $this->usersRepository([]);

        $action = new FindUserByUsername($usersRepository);

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such query param in the request: username"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfUserNotFound(): void
    {
        $request = new Request(['username' => 'ivan'], [], '');

        $userRepository = $this->usersRepository([]);
        $action = new FindUserByUsername($userRepository);
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
    public function testItReturnsSuccessfulResponse(): void
    {
        $request = new Request(['username' => 'ivan'], [], '');

        $userRepository = $this->usersRepository([
            new User(
                new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
                'ivan',
                new Name('Ivan', 'Nikitin')
            ),
        ]);

        $action = new FindUserByUsername($userRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $this->expectOutputString('{"success":true,"data":{"uuid":"10373537-0805-4d7a-830e-22b481b4859c","username":"ivan","first_name":"Ivan","last_name":"Nikitin"}}');

        $response->send();
    }
}