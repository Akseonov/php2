<?php

namespace Akseonov\Php2\UnitTests\Actions\Comments;

use Akseonov\Php2\Actions\Comments\FindCommentByUuid;
use Akseonov\Php2\Blog\Comment;
use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentsRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\CommentNotFoundException;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\SuccessfulResponse;
use Akseonov\Php2\Person\Name;
use PHPUnit\Framework\TestCase;
use JsonException;

class FindCommentByUuidActionTest extends TestCase
{
    private function commentsRepository(array $comments): CommentsRepositoryInterface
    {
        return new class($comments) implements CommentsRepositoryInterface
        {
            public function __construct(
                private array $comments
            )
            {
            }

            public function save(Comment $comment): void
            {
            }

            public function get(UUID $uuid): Comment
            {
                foreach ($this->comments as $comment) {
                    if ($comment instanceof Comment && (string)$uuid === $comment->getUuid()) {
                        return $comment;
                    }
                }
                throw new CommentNotFoundException('Not found');
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

        $repository = $this->commentsRepository([]);

        $action = new FindCommentByUuid($repository);

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
    public function testItReturnsErrorResponseIfCommentNotFound(): void
    {
        $request = new Request([
            'uuid' => 'a3e78b09-23ae-44fd-9939-865f688894f5'
        ], [], '');

        $repository = $this->commentsRepository([]);

        $action = new FindCommentByUuid($repository);

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

        $repository = $this->commentsRepository([]);

        $action = new FindCommentByUuid($repository);

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



        $repository = $this->commentsRepository([
            new Comment(
                new UUID('2ef8f342-6a5c-4e7c-b39f-5d688f0fce10'),
                $post,
                $user,
                'text'
            )
        ]);

        $action = new FindCommentByUuid($repository);

        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $this->expectOutputString('{"success":true,"data":{"uuid":"2ef8f342-6a5c-4e7c-b39f-5d688f0fce10","author_uuid":"10373537-0805-4d7a-830e-22b481b4859c","post_uuid":"a3e78b09-23ae-44fd-9939-865f688894f5","text":"text"}}');

        $response->send();
    }
}