<?php

namespace Akseonov\Php2\UnitTests\http\Actions\Auth;

use Akseonov\Php2\Blog\AuthToken;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\AuthTokensRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\AuthException;
use Akseonov\Php2\Exceptions\AuthTokensRepositoryException;
use Akseonov\Php2\http\Actions\Auth\LogIn;
use Akseonov\Php2\http\Auth\Interfaces\PasswordAuthenticationInterface;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\SuccessfulResponse;
use Akseonov\Php2\Person\Name;
use Akseonov\Php2\UnitTests\DummyLogger;
use DateTimeImmutable;
use Exception;
use JsonException;
use Monolog\Test\TestCase;

class LogInTest extends TestCase
{
    private  function passwordAuthenticationException(User $user): PasswordAuthenticationInterface {
        return new class($user) implements PasswordAuthenticationInterface {
            public function __construct(
            )
            {
            }

            public function user(Request $request): User
            {
                throw new AuthException('Not found');
            }
        };
    }

    private  function passwordAuthentication(User $user): PasswordAuthenticationInterface {
        return new class($user) implements PasswordAuthenticationInterface {
            public function __construct(
                private readonly User $user
            )
            {
            }

            public function user(Request $request): User
            {
                return $this->user;
            }
        };
    }

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
                throw new AuthTokensRepositoryException('Not found');
            }
        };
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     * @throws Exception
     */
    public function testItReturnsErrorResponseIfCannotDecodeJsonBody(): void
    {
        $request = new Request([], [], '{}');

        $passwordAuthentication = $this->passwordAuthenticationException(
            new User(
                new UUID('3a6ec3a6-f25b-438a-93d4-8392f620a702'),
                'ivan',
                '12345',
                new Name('Ivan', 'Nikitin')
            )
        );

        $tokenAuthentication = $this->tokenAuthenticationUserReturn(
            new AuthToken(
                '4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52',
                new UUID('3a6ec3a6-f25b-438a-93d4-8392f620a702'),
                new DateTimeImmutable('2022-08-29T11:02:35+00:00')
            )
        );

        $action = new LogIn(
            $passwordAuthentication,
            $tokenAuthentication,
            new DummyLogger()
        );

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Not found"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     * @throws Exception
     */
    public function testItReturnsSuccessfulResponse(): void
    {
        $request = new Request([], [], '{}');

        $passwordAuthentication = $this->passwordAuthentication(
            new User(
                new UUID('3a6ec3a6-f25b-438a-93d4-8392f620a702'),
                'ivan',
                '12345',
                new Name('Ivan', 'Nikitin')
            )
        );

        $tokenAuthentication = $this->tokenAuthenticationUserReturn(
            new AuthToken(
                '4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52',
                new UUID('3a6ec3a6-f25b-438a-93d4-8392f620a702'),
                new DateTimeImmutable('2022-08-29T11:02:35+00:00')
            )
        );

        $action = new LogIn(
            $passwordAuthentication,
            $tokenAuthentication,
            new DummyLogger()
        );

        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $this->expectOutputString('{"success":true,"data":{"token":"d29adced1981aceea1a631071d23a3933632ff70ecf4ee2b352124d9945243ed258decca9b349465"}}');
        $this->setOutputCallback(function ($data){
            $dataDecode = json_decode(
                $data,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );
            var_dump($dataDecode);
            $dataDecode['data']['token'] = "d29adced1981aceea1a631071d23a3933632ff70ecf4ee2b352124d9945243ed258decca9b349465";
            return json_encode(
                $dataDecode,
                JSON_THROW_ON_ERROR
            );
        });

        $response->send();
    }
}