<?php

namespace Akseonov\Php2\UnitTests\Actions\Users;

use Akseonov\Php2\Actions\Users\CreateUser;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\Exceptions\JsonException;
use Akseonov\Php2\http\SuccessfulResponse;
use PHPUnit\Framework\TestCase;

class CreateUserActionTest extends TestCase
{
    private function usersRepository(): UsersRepositoryInterface
    {
        return new class() implements UsersRepositoryInterface
        {
            private bool $called = false;

            public function __construct()
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

        $action = new CreateUser($usersRepository);

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

        $action = new CreateUser($usersRepository);

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

        $action = new CreateUser($usersRepository);

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

        $action = new CreateUser($usersRepository);

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
    public function testItReturnsSuccessfulResponse(): void
    {
        $request = new Request([], [], '{"username":"hello","first_name":"bye","last_name":"my"}');

        $usersRepository = $this->usersRepository([]);

        $action = new CreateUser($usersRepository);

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