<?php

namespace Akseonov\Php2\UnitTests\Actions\Likes;

use Akseonov\Php2\Actions\Likes\CreateCommentLike;
use Akseonov\Php2\Actions\Likes\CreatePostLike;
use Akseonov\Php2\Blog\Comment;
use Akseonov\Php2\Blog\CommentLike;
use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentLikesRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentsRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostsRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\CommentNotFoundException;
use Akseonov\Php2\Exceptions\LikesCommentNotFoundException;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\SuccessfulResponse;
use Akseonov\Php2\Person\Name;
use JsonException;
use PHPUnit\Framework\TestCase;

class CreateCommentLikeActionTest extends TestCase
{
    private function commentLikesRepository(array $likes): CommentLikesRepositoryInterface
    {
        return new class($likes) implements CommentLikesRepositoryInterface
        {
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

    private function postsRepository(array $posts): PostsRepositoryInterface
    {
        return new class($posts) implements PostsRepositoryInterface
        {
            public function __construct(
                private readonly array $posts
            )
            {
            }

            public function save(Post $post): void
            {
            }

            public function get(UUID $uuid): Post
            {
                foreach ($this->posts as $post) {
                    if ($post instanceof Post && (string)$uuid === $post->getUuid()) {
                        return $post;
                    }
                }
                throw new PostNotFoundException('Not found');
            }

            public function getByTitle(string $title): Post
            {
                throw new PostNotFoundException('Not found');
            }

            public function delete(UUID $uuid): void
            {
            }
        };
    }

    private function usersRepository(array $users): UsersRepositoryInterface
    {
        return new class($users) implements UsersRepositoryInterface
        {
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
                foreach ($this->users as $user) {
                    if ($user instanceof User && (string)$uuid === $user->getUuid()) {
                        return $user;
                    }
                }
                throw new UserNotFoundException('Not found');
            }

            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException('Not found');
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
        $usersRepository = $this->usersRepository([]);

        $action = new CreateCommentLike($commentLikesRepository,  $commentsRepository, $usersRepository);

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
        $request = new Request([], [], '{}');

        $commentLikesRepository = $this->commentLikesRepository([]);
        $commentsRepository = $this->commentsRepository([]);
        $usersRepository = $this->usersRepository([]);

        $action = new CreateCommentLike($commentLikesRepository, $commentsRepository, $usersRepository);

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such Field: user_uuid"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfNoPostUuidProvided(): void
    {
        $request = new Request([], [], '{"user_uuid":"a3e78b09-23ae-44fd-9939-865f688894f5"}');

        $commentLikesRepository = $this->commentLikesRepository([]);
        $commentsRepository = $this->commentsRepository([]);
        $usersRepository = $this->usersRepository([]);

        $action = new CreateCommentLike($commentLikesRepository, $commentsRepository, $usersRepository);

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
        $request = new Request([], [], '{"user_uuid":"a3e78b09-23ae-44fd-9939-865f688894f5","comment_uuid":"2ef8f342-6a5c-4e7c-b39f-5d688f0fce10"}');

        $commentLikesRepository = $this->commentLikesRepository([]);
        $commentsRepository = $this->commentsRepository([]);
        $usersRepository = $this->usersRepository([]);

        $action = new CreateCommentLike($commentLikesRepository, $commentsRepository, $usersRepository);

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
        $request = new Request([], [], '{"user_uuid":"a3e78b09-23ae-44fd-9939-865f688894f5","comment_uuid":"2ef8f342-6a5c-4e7c-b39f-5d688f0fce10","text":"text"}');

        $commentLikesRepository = $this->commentLikesRepository([]);
        $commentsRepository = $this->commentsRepository([]);
        $usersRepository = $this->usersRepository([
            new User(
                new UUID('a3e78b09-23ae-44fd-9939-865f688894f5'),
                'username',
                new Name('name', 'surname'),
            )
        ]);

        $action = new CreateCommentLike($commentLikesRepository, $commentsRepository, $usersRepository);

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
        $request = new Request([], [], '{"user_uuid":"a3e78b09-23ae-44fd-9939-865f688894f5","comment_uuid":"10373537-0805-4d7a-830e-22b481b4859c"}');

        $user = new User(
            new UUID('a3e78b09-23ae-44fd-9939-865f688894f5'),
            'username',
            new Name('name', 'surname'),
        );

        $post = new Post(
            new UUID('2ef8f342-6a5c-4e7c-b39f-5d688f0fce10'),
            $user,
            'title',
            'text'
        );

        $commentLikesRepository = $this->commentLikesRepository([]);
        $commentsRepository = $this->commentsRepository([
            new Comment(
                new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
                $post,
                $user,
                'text'
            )
        ]);
        $usersRepository = $this->usersRepository([
            $user
        ]);

        $action = new CreateCommentLike($commentLikesRepository, $commentsRepository, $usersRepository);

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