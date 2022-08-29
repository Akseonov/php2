<?php

namespace Akseonov\Php2\UnitTests\http\Actions\Posts;

use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostsRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\AuthException;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\http\Actions\Posts\CreatePost;
use Akseonov\Php2\http\Auth\Interfaces\TokenAuthenticationInterface;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\SuccessfulResponse;
use Akseonov\Php2\Person\Name;
use Akseonov\Php2\UnitTests\DummyLogger;
use JsonException;
use PHPUnit\Framework\TestCase;

class CreatePostActionTest extends TestCase
{
    private function postsRepository(): PostsRepositoryInterface
    {
        return new class() implements PostsRepositoryInterface {
            private bool $called = false;

            public function __construct()
            {
            }

            public function save(Post $post): void
            {
                $this->called = true;
            }

            public function get(UUID $uuid): Post
            {
                throw new PostNotFoundException('Not found');
            }

            public function getByTitle(string $title): Post
            {
                throw new PostNotFoundException('Not found');
            }

            public function getCalled(): bool
            {
                return $this->called;
            }

            public function delete(UUID $uuid): void
            {
            }
        };
    }

    private function tokenAuthenticationEmpty(User $user): TokenAuthenticationInterface
    {
        return new class($user) implements TokenAuthenticationInterface {
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

    private function tokenAuthenticationUserReturn(User $user): TokenAuthenticationInterface
    {
        return new class($user) implements TokenAuthenticationInterface {
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

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfCannotDecodeJsonBody(): void
    {
        $request = new Request([], [
            'HTTP_AUTHORIZATION' => 'Bearer 02c9747457eec4d4fdf9edecab4df9233d1a4d71db53a9d5c44942b01fd9e46c8d7a1a671194c679'
        ], "");

        $postsRepository = $this->postsRepository([]);
        $tokenAuthentication = $this->tokenAuthenticationUserReturn(
            new User(
                new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
                'ivan',
                '12345',
                new Name('Ivan', 'Nikitin')
            )
        );

        $action = new CreatePost($postsRepository, $tokenAuthentication, new DummyLogger());

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
    public function testItReturnsErrorResponseIfNoAuthorUuidProvided(): void
    {
        $request = new Request([], [
            'HTTP_AUTHORIZATION' => 'Bearer 02c9747457eec4d4fdf9edecab4df9233d1a4d71db53a9d5c44942b01fd9e46c8d7a1a671194c679'
        ], '{"uuid":"2342342"}');

        $postsRepository = $this->postsRepository([]);
        $tokenAuthentication = $this->tokenAuthenticationUserReturn(
            new User(
                new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
                'ivan',
                '12345',
                new Name('Ivan', 'Nikitin')
            )
        );

        $action = new CreatePost($postsRepository, $tokenAuthentication, new DummyLogger());

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such Field: title"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfNoTextProvided(): void
    {
        $request = new Request([], [
            'HTTP_AUTHORIZATION' => 'Bearer 02c9747457eec4d4fdf9edecab4df9233d1a4d71db53a9d5c44942b01fd9e46c8d7a1a671194c679'
        ], '{"title":"title"}');

        $postsRepository = $this->postsRepository([]);
        $tokenAuthentication = $this->tokenAuthenticationUserReturn(
            new User(
                new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
                'ivan',
                '12345',
                new Name('Ivan', 'Nikitin')
            )
        );

        $action = new CreatePost($postsRepository, $tokenAuthentication, new DummyLogger());

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such Field: text"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfHeaderNotProvided(): void
    {
        $request = new Request([], [], '{"title":"title","text":"text"}');

        $postsRepository = $this->postsRepository([]);
        $tokenAuthentication = $this->tokenAuthenticationEmpty(
            new User(
                new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
                'ivan',
                '12345',
                new Name('Ivan', 'Nikitin')
            )
        );

        $action = new CreatePost($postsRepository, $tokenAuthentication, new DummyLogger());

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
        $request = new Request([], [], '{"title":"title","text":"text"}');

        $postsRepository = $this->postsRepository([]);

        $tokenAuthentication = $this->tokenAuthenticationUserReturn(
            new User(
                new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
                'username',
                '12345',
                new Name('name', 'surname')
            ),
        );

        $action = new CreatePost($postsRepository, $tokenAuthentication, new DummyLogger());

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