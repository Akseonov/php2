<?php

namespace Akseonov\Php2\UnitTests\http\Actions\Users;

use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\JsonException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\Actions\Users\CreateUser;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\SuccessfulResponse;
use Akseonov\Php2\Person\Name;
use Akseonov\Php2\UnitTests\DummyLogger;
use PHPUnit\Framework\TestCase;

class CreateUserActionTest extends TestCase
{
    private function usersRepository(array $users): UsersRepositoryInterface
    {
        return new class($users) implements UsersRepositoryInterface
        {
            private bool $called = false;

            public function __construct(
                private array $users,
            )
            {
            }

            public function save(User $user): void
            {
                $this->called = true;
            }

            public function get(UUID $uuid): User
            {
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

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfCannotDecodeJsonBody(): void
    {
        $request = new Request([], [], "");

        $usersRepository = $this->usersRepository([]);

        $action = new CreateUser($usersRepository, new DummyLogger());

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Cannot decode json body"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfNoUsernameProvided(): void
    {
        $request = new Request([], [], '{"uuid":"2342342"}');

        $usersRepository = $this->usersRepository([]);

        $action = new \Akseonov\Php2\http\Actions\Users\CreateUser($usersRepository, new DummyLogger());

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such Field: username"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfNoFirstNameProvided(): void
    {
        $request = new Request([], [], '{"username":"hello"}');

        $usersRepository = $this->usersRepository([]);

        $action = new CreateUser($usersRepository, new DummyLogger());

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such Field: first_name"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfNoLastNameProvided(): void
    {
        $request = new Request([], [], '{"username":"hello","first_name":"bye"}');

        $usersRepository = $this->usersRepository([]);

        $action = new CreateUser($usersRepository, new DummyLogger());

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such Field: last_name"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfUserAlreadyExists(): void
    {
        $request = new Request([], [], '{"username":"hello","first_name":"bye","last_name":"my"}');

        $usersRepository = $this->usersRepository([
            new User(
                new UUID('badad97b-a156-47f0-83b6-ea5f5fc8b044'),
                'hello',
                new Name('first', 'last')
            )
        ]);

        $action = new CreateUser($usersRepository, new DummyLogger());

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"User already exists: hello"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsSuccessfulResponse(): void
    {
        $request = new Request([], [], '{"username":"hello","first_name":"bye","last_name":"my"}');

        $usersRepository = $this->usersRepository([]);

        $action = new CreateUser($usersRepository, new DummyLogger());

        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $this->expectOutputString('{"success":true,"data":{"uuid":"351739ab-fc33-49ae-a62d-b606b7038c87"}}');
        $this->setOutputCallback(function ($data){
            $dataDecode = json_decode(
                    $data,
                    associative: true,
                    flags: JSON_THROW_ON_ERROR
                );
            var_dump($dataDecode);
            $dataDecode['data']['uuid'] = "351739ab-fc33-49ae-a62d-b606b7038c87";
            return json_encode(
                $dataDecode,
                JSON_THROW_ON_ERROR
            );
        });

        $response->send();
    }
}