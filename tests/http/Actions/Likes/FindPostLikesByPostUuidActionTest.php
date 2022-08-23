<?php

namespace Akseonov\Php2\UnitTests\http\Actions\Likes;

use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\PostLike;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostLikesRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\LikesPostNotFoundException;
use Akseonov\Php2\http\Actions\Likes\FindPostLikesByPostUuid;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\SuccessfulResponse;
use Akseonov\Php2\Person\Name;
use Akseonov\Php2\UnitTests\DummyLogger;
use JsonException;
use PHPUnit\Framework\TestCase;

class FindPostLikesByPostUuidActionTest extends TestCase
{
    private function postLikesRepository(array $likes): PostLikesRepositoryInterface
    {
        return new class($likes) implements PostLikesRepositoryInterface
        {
            public function __construct(
                private readonly array $likes
            )
            {
            }

            public function save(PostLike $postLike): void
            {
            }

            public function getByPostUuid(UUID $uuid): array
            {
                $likes = [];

                foreach ($this->likes as $like) {
                    if ($like instanceof PostLike && (string)$uuid === $like->getUuid()) {
                        $likes[] = $like;
                    }
                }

                if (!empty($likes)) {
                    return $likes;
                }

                throw new LikesPostNotFoundException('Not found');
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

        $request = new Request([], [], '');

        $repository = $this->postLikesRepository([]);

        $action = new FindPostLikesByPostUuid($repository, new DummyLogger());

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
    public function testItReturnsErrorResponseIfPostLikesNotFound(): void
    {
        $request = new Request([
            'uuid' => 'a3e78b09-23ae-44fd-9939-865f688894f5'
        ], [], '');

        $repository = $this->postLikesRepository([]);

        $action = new FindPostLikesByPostUuid($repository, new DummyLogger());

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
            'uuid' => 'a3e78b09-23ae-44fd'
        ], [], '');

        $repository = $this->postLikesRepository([]);

        $action = new FindPostLikesByPostUuid($repository, new DummyLogger());

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Malformed UUID: a3e78b09-23ae-44fd"}');

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
            'uuid' => '2ef8f342-6a5c-4e7c-b39f-5d688f0fce10'
        ], [], '');

        $user = new User(
            new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
            'username',
            new Name('name', 'surname')
        );

        $post = new Post(
            new UUID('a3e78b09-23ae-44fd-9939-865f688894f5'),
            $user,
            'title',
            'text'
        );

        $repository = $this->postLikesRepository([
            new PostLike(
                new UUID('2ef8f342-6a5c-4e7c-b39f-5d688f0fce10'),
                $post,
                $user,
            )
        ]);

        $action = new FindPostLikesByPostUuid($repository, new DummyLogger());

        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $this->expectOutputString('{"success":true,"data":[{"uuid":"2ef8f342-6a5c-4e7c-b39f-5d688f0fce10","post":{"uuid":"a3e78b09-23ae-44fd-9939-865f688894f5","user":{"uuid":"10373537-0805-4d7a-830e-22b481b4859c","username":"username","first_name":"name","last_name":"surname"},"title":"title","text":"text"},"user":{"uuid":"10373537-0805-4d7a-830e-22b481b4859c","username":"username","first_name":"name","last_name":"surname"}}]}');

        $response->send();
    }
}