<?php

namespace Akseonov\Php2\UnitTests\http\Actions\Likes;

use Akseonov\Php2\Blog\Comment;
use Akseonov\Php2\Blog\CommentLike;
use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentLikesRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentsRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\AuthException;
use Akseonov\Php2\Exceptions\CommentNotFoundException;
use Akseonov\Php2\Exceptions\LikesCommentNotFoundException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\Actions\Likes\CreateCommentLike;
use Akseonov\Php2\http\Auth\Interfaces\TokenAuthenticationInterface;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\SuccessfulResponse;
use Akseonov\Php2\Person\Name;
use Akseonov\Php2\UnitTests\DummyLogger;
use JsonException;
use PHPUnit\Framework\TestCase;

class CreateCommentLikeActionTest extends TestCase
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

            private bool $called = false;

            public function save(CommentLike $commentLike): void
            {
                $this->called = true;
            }

            public function getCalled(): bool
            {
                return $this->called;
            }

            public function getByCommentUuid(UUID $uuid): array
            {
                $likes = [];

                foreach ($this->likes as $like) {
                    if ($like instanceof CommentLike && (string)$uuid === $like->getComment()->getUuid()) {
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

    private function commentsRepository(array $comments): CommentsRepositoryInterface
    {
        return new class($comments) implements CommentsRepositoryInterface
        {
            public function __construct(
                private readonly array $comments
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
        $request = new Request([], [], "");

        $commentLikesRepository = $this->commentLikesRepository([]);
        $commentsRepository = $this->commentsRepository([]);
        $tokenAuthentication = $this->tokenAuthenticationUserReturn(
            new User(
                new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
                'ivan',
                '12345',
                new Name('Ivan', 'Nikitin')
            )
        );

        $action = new CreateCommentLike(
            $commentLikesRepository,
            $commentsRepository,
            $tokenAuthentication,
            new DummyLogger()
        );

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
    public function testItReturnsErrorResponseIfNoPostUuidProvided(): void
    {
        $request = new Request([], [], '{}');

        $commentLikesRepository = $this->commentLikesRepository([]);
        $commentsRepository = $this->commentsRepository([]);
        $tokenAuthentication = $this->tokenAuthenticationUserReturn(
            new User(
                new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
                'ivan',
                '12345',
                new Name('Ivan', 'Nikitin')
            )
        );

        $action = new CreateCommentLike(
            $commentLikesRepository,
            $commentsRepository,
            $tokenAuthentication,
            new DummyLogger()
        );

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such Field: comment_uuid"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfUserNotFound(): void
    {
        $request = new Request([], [], '{"comment_uuid":"2ef8f342-6a5c-4e7c-b39f-5d688f0fce10"}');

        $commentLikesRepository = $this->commentLikesRepository([]);
        $commentsRepository = $this->commentsRepository([]);
        $tokenAuthentication = $this->tokenAuthenticationUserReturn(
            new User(
                new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
                'ivan',
                '12345',
                new Name('Ivan', 'Nikitin')
            )
        );

        $action = new CreateCommentLike(
            $commentLikesRepository,
            $commentsRepository,
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
     */
    public function testItReturnsErrorResponseIfPostNotFound(): void
    {
        $request = new Request([], [], '{"comment_uuid":"2ef8f342-6a5c-4e7c-b39f-5d688f0fce10","text":"text"}');

        $commentLikesRepository = $this->commentLikesRepository([]);
        $commentsRepository = $this->commentsRepository([]);
        $tokenAuthentication = $this->tokenAuthenticationUserReturn(
            new User(
                new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
                'ivan',
                '12345',
                new Name('Ivan', 'Nikitin')
            )
        );

        $action = new CreateCommentLike(
            $commentLikesRepository,
            $commentsRepository,
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
     */
    public function testItReturnsErrorResponseIfCommentLikeAlreadyExist(): void
    {
        $request = new Request([], [], '{"comment_uuid":"10373537-0805-4d7a-830e-22b481b4859c"}');

        $user = new User(
            new UUID('a3e78b09-23ae-44fd-9939-865f688894f5'),
            'username',
            '12345',
            new Name('name', 'surname'),
        );

        $post = new Post(
            new UUID('2ef8f342-6a5c-4e7c-b39f-5d688f0fce10'),
            $user,
            'title',
            'text'
        );

        $comment = new Comment(
            new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
            $post,
            $user,
            'text'
        );

        $commentLikesRepository = $this->commentLikesRepository([
            new CommentLike(
                new UUID('05c6ac89-6b14-4343-811e-ebd8823ec8fc'),
                $comment,
                $user
            )
        ]);
        $commentsRepository = $this->commentsRepository([
            $comment
        ]);
        $tokenAuthentication = $this->tokenAuthenticationUserReturn(
            $user
        );

        $action = new CreateCommentLike(
            $commentLikesRepository,
            $commentsRepository,
            $tokenAuthentication,
            new DummyLogger()
        );

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Post like already exists: a3e78b09-23ae-44fd-9939-865f688894f5, 10373537-0805-4d7a-830e-22b481b4859c"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfAuthTokenNotProvided(): void
    {
        $request = new Request([], [], '{"comment_uuid":"10373537-0805-4d7a-830e-22b481b4859c"}');

        $user = new User(
            new UUID('a3e78b09-23ae-44fd-9939-865f688894f5'),
            'username',
            '12345',
            new Name('name', 'surname'),
        );

        $post = new Post(
            new UUID('2ef8f342-6a5c-4e7c-b39f-5d688f0fce10'),
            $user,
            'title',
            'text'
        );

        $comment = new Comment(
            new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
            $post,
            $user,
            'text'
        );

        $commentLikesRepository = $this->commentLikesRepository([
            new CommentLike(
                new UUID('05c6ac89-6b14-4343-811e-ebd8823ec8fc'),
                $comment,
                $user
            )
        ]);
        $commentsRepository = $this->commentsRepository([
            $comment
        ]);
        $tokenAuthentication = $this->tokenAuthenticationEmpty(
            $user
        );

        $action = new CreateCommentLike(
            $commentLikesRepository,
            $commentsRepository,
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
     */
    public function testItReturnsSuccessfulResponse(): void
    {
        $request = new Request([], [], '{"comment_uuid":"10373537-0805-4d7a-830e-22b481b4859c"}');

        $user = new User(
            new UUID('a3e78b09-23ae-44fd-9939-865f688894f5'),
            'username',
            '12345',
            new Name('name', 'surname'),
        );

        $post = new Post(
            new UUID('2ef8f342-6a5c-4e7c-b39f-5d688f0fce10'),
            $user,
            'title',
            'text'
        );

        $comment = new Comment(
            new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
            $post,
            $user,
            'text'
        );

        $commentLikesRepository = $this->commentLikesRepository([
            new CommentLike(
                new UUID('05c6ac89-6b14-4343-811e-ebd8823ec8fc'),
                $comment,
                $user
            )
        ]);
        $commentsRepository = $this->commentsRepository([
            $comment
        ]);
        $tokenAuthentication = $this->tokenAuthenticationUserReturn(
            new User(
                new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
                'ivan',
                '12345',
                new Name('Ivan', 'Nikitin')
            )
        );

        $action = new CreateCommentLike(
            $commentLikesRepository,
            $commentsRepository,
            $tokenAuthentication,
            new DummyLogger()
        );

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