<?php

namespace Akseonov\Php2\UnitTests\http\Actions\Likes;

use Akseonov\Php2\Blog\Comment;
use Akseonov\Php2\Blog\CommentLike;
use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentLikesRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\LikesCommentNotFoundException;
use Akseonov\Php2\http\Actions\Likes\FindCommentLikesByCommentUuid;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\SuccessfulResponse;
use Akseonov\Php2\Person\Name;
use Akseonov\Php2\UnitTests\DummyLogger;
use JsonException;
use PHPUnit\Framework\TestCase;

class FindCommentLikesByCommentUuidActionTest extends TestCase
{
    private function commentLikesRepository(array $likes): CommentLikesRepositoryInterface
    {
        return new class($likes) implements CommentLikesRepositoryInterface
        {
            public function __construct(
                private readonly array $likes
            )
            {
            }

            public function save(CommentLike $commentLike): void
            {
            }

            public function getByCommentUuid(UUID $uuid): array
            {
                $likes = [];

                foreach ($this->likes as $like) {
                    if ($like instanceof CommentLike && (string)$uuid === $like->getUuid()) {
                        $likes[] = $like;
                    }
                }

                if (!empty($likes)) {
                    return $likes;
                }

                throw new LikesCommentNotFoundException('Not found');
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

        $repository = $this->commentLikesRepository([]);

        $action = new FindCommentLikesByCommentUuid($repository, new DummyLogger());

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

        $repository = $this->commentLikesRepository([]);

        $action = new \Akseonov\Php2\http\Actions\Likes\FindCommentLikesByCommentUuid($repository, new DummyLogger());

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

        $repository = $this->commentLikesRepository([]);

        $action = new FindCommentLikesByCommentUuid($repository, new DummyLogger());

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
            '12345',
            new Name('name', 'surname')
        );

        $post = new Post(
            new UUID('a3e78b09-23ae-44fd-9939-865f688894f5'),
            $user,
            'title',
            'text'
        );

        $comment = new Comment(
            new UUID('b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481'),
            $post,
            $user,
            'text'
        );

        $repository = $this->commentLikesRepository([
            new CommentLike(
                new UUID('2ef8f342-6a5c-4e7c-b39f-5d688f0fce10'),
                $comment,
                $user,
            )
        ]);

        $action = new FindCommentLikesByCommentUuid($repository, new DummyLogger());

        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $this->expectOutputString('{"success":true,"data":[{"uuid":"2ef8f342-6a5c-4e7c-b39f-5d688f0fce10","comment":{"uuid":"b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481","post":{"uuid":"a3e78b09-23ae-44fd-9939-865f688894f5","user":{"uuid":"10373537-0805-4d7a-830e-22b481b4859c","username":"username","first_name":"name","last_name":"surname"},"title":"title","text":"text"},"user":{"uuid":"10373537-0805-4d7a-830e-22b481b4859c","username":"username","first_name":"name","last_name":"surname"},"text":"text"},"user":{"uuid":"10373537-0805-4d7a-830e-22b481b4859c","username":"username","first_name":"name","last_name":"surname"}}]}');

        $response->send();
    }
}