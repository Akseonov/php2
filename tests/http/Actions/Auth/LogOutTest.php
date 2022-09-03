<?php

namespace Akseonov\Php2\UnitTests\http\Actions\Auth;

use Akseonov\Php2\Blog\AuthToken;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\AuthTokensRepositoryInterface;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\AuthTokenNotFoundException;
use Akseonov\Php2\Exceptions\AuthTokensRepositoryException;
use Akseonov\Php2\http\Actions\Auth\LogOut;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\SuccessfulResponse;
use Akseonov\Php2\UnitTests\DummyLogger;
use DateTimeImmutable;
use Exception;
use Monolog\Test\TestCase;

class LogOutTest extends TestCase
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

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws Exception
     */
    public function testItReturnsErrorResponseIfHeaderNotProvided(): void
    {
        $request = new Request([], [], '{}');

        $tokenAuthentication = $this->tokenAuthenticationUserReturn(
            new AuthToken(
                '4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52',
                new UUID('3a6ec3a6-f25b-438a-93d4-8392f620a702'),
                new DateTimeImmutable('2022-08-29T11:02:35+00:00')
            )
        );

        $action = new LogOut(
            $tokenAuthentication,
            new DummyLogger()
        );

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Not such header in request: Authorization"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws Exception
     */
    public function testItReturnsErrorResponseIfHeaderEmpty(): void
    {
        $request = new Request([], [
            'HTTP_AUTHORIZATION' => ''
        ], '{}');

        $tokenAuthentication = $this->tokenAuthenticationUserReturn(
            new AuthToken(
                '4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52',
                new UUID('3a6ec3a6-f25b-438a-93d4-8392f620a702'),
                new DateTimeImmutable('2022-08-29T11:02:35+00:00')
            )
        );

        $action = new LogOut(
            $tokenAuthentication,
            new DummyLogger()
        );

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Empty header in request: "}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws Exception
     */
    public function testItReturnsErrorResponseIfPrefixNotProvided(): void
    {
        $request = new Request([], [
            'HTTP_AUTHORIZATION' => '214'
        ], '{}');

        $tokenAuthentication = $this->tokenAuthenticationUserReturn(
            new AuthToken(
                '4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52',
                new UUID('3a6ec3a6-f25b-438a-93d4-8392f620a702'),
                new DateTimeImmutable('2022-08-29T11:02:35+00:00')
            )
        );

        $action = new LogOut(
            $tokenAuthentication,
            new DummyLogger()
        );

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Malformed token: [214]"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws Exception
     */
    public function testItReturnsErrorResponseIfAuthTokenNotFound(): void
    {
        $request = new Request([], [
            'HTTP_AUTHORIZATION' => 'Bearer 1234'
        ], '{}');

        $tokenAuthentication = $this->tokenAuthenticationUserReturn(
            new AuthToken(
                '4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52',
                new UUID('3a6ec3a6-f25b-438a-93d4-8392f620a702'),
                new DateTimeImmutable('2022-08-29T11:02:35+00:00')
            )
        );

        $action = new LogOut(
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
     * @throws Exception
     */
    public function testItReturnsSuccessfulResponse(): void
    {
        $request = new Request([], [
            'HTTP_AUTHORIZATION' => 'Bearer 4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52'
        ], '{}');

        $tokenAuthentication = $this->tokenAuthenticationUserReturn(
            new AuthToken(
                '4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52',
                new UUID('3a6ec3a6-f25b-438a-93d4-8392f620a702'),
                new DateTimeImmutable('2022-08-29T11:02:35+00:00')
            )
        );

        $action = new LogOut(
            $tokenAuthentication,
            new DummyLogger()
        );

        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $this->expectOutputString('{"success":true,"data":{"token":"4f6216269ad31d93d0a2f1fca264adabaf6ffc2cbeda903a4879fdfb5a53e9d59ad7141b979e0d52"}}');

        $response->send();
    }
}