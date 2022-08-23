<?php

namespace Akseonov\Php2\UnitTests\http\Actions\Comments;

use Akseonov\Php2\Blog\Comment;
use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentsRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostsRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\CommentNotFoundException;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\Actions\Comments\CreateComment;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\SuccessfulResponse;
use Akseonov\Php2\Person\Name;
use Akseonov\Php2\UnitTests\DummyLogger;
use JsonException;
use PHPUnit\Framework\TestCase;

class CreateCommentActionTest extends TestCase
{
    private function commentsRepository(array $comments): CommentsRepositoryInterface
    {
        return new class($comments) implements CommentsRepositoryInterface
        {
            private bool $called = false;

            public function save(Comment $comment): void
            {
                $this->called = true;
            }

            public function get(UUID $uuid): Comment
            {
                throw new CommentNotFoundException('Not found');
            }

            public function getCalled(): bool
            {
                return $this->called;
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
                private readonly array $users
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

        $commentsRepository = $this->commentsRepository([]);
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([]);

        $action = new \Akseonov\Php2\http\Actions\Comments\CreateComment(
            $commentsRepository,
            $postsRepository,
            $usersRepository,
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
    public function testItReturnsErrorResponseIfNoAuthorUuidProvided(): void
    {
        $request = new Request([], [], '{}');

        $commentsRepository = $this->commentsRepository([]);
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([]);

        $action = new \Akseonov\Php2\http\Actions\Comments\CreateComment(
            $commentsRepository,
            $postsRepository,
            $usersRepository,
            new DummyLogger()
        );

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such Field: author_uuid"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfNoPostUuidProvided(): void
    {
        $request = new Request([], [], '{"author_uuid":"a3e78b09-23ae-44fd-9939-865f688894f5"}');

        $commentsRepository = $this->commentsRepository([]);
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([]);

        $action = new CreateComment(
            $commentsRepository,
            $postsRepository,
            $usersRepository,
            new DummyLogger()
        );

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such Field: post_uuid"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfNoTextProvided(): void
    {
        $request = new Request([], [], '{"author_uuid":"a3e78b09-23ae-44fd-9939-865f688894f5","post_uuid":"2ef8f342-6a5c-4e7c-b39f-5d688f0fce10"}');

        $user = new User(
            new UUID('a3e78b09-23ae-44fd-9939-865f688894f5'),
            'username',
            new Name('name', 'surname'),
        );

        $commentsRepository = $this->commentsRepository([]);
        $postsRepository = $this->postsRepository([
            new Post(
                new UUID('2ef8f342-6a5c-4e7c-b39f-5d688f0fce10'),
                $user,
                'title',
                'text'
            )
        ]);
        $usersRepository = $this->usersRepository([
            $user
        ]);

        $action = new \Akseonov\Php2\http\Actions\Comments\CreateComment(
            $commentsRepository,
            $postsRepository,
            $usersRepository,
            new DummyLogger()
        );

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
    public function testItReturnsErrorResponseIfUserNotFound(): void
    {
        $request = new Request([], [], '{"author_uuid":"a3e78b09-23ae-44fd-9939-865f688894f5","post_uuid":"2ef8f342-6a5c-4e7c-b39f-5d688f0fce10"}');

        $commentsRepository = $this->commentsRepository([]);
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([]);

        $action = new \Akseonov\Php2\http\Actions\Comments\CreateComment(
            $commentsRepository,
            $postsRepository,
            $usersRepository,
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
        $request = new Request([], [], '{"author_uuid":"a3e78b09-23ae-44fd-9939-865f688894f5","post_uuid":"2ef8f342-6a5c-4e7c-b39f-5d688f0fce10","text":"text"}');

        $commentsRepository = $this->commentsRepository([]);
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([
            new User(
                new UUID('a3e78b09-23ae-44fd-9939-865f688894f5'),
                'username',
                new Name('name', 'surname'),
            )
        ]);

        $action = new \Akseonov\Php2\http\Actions\Comments\CreateComment(
            $commentsRepository,
            $postsRepository,
            $usersRepository,
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
        $request = new Request([], [], '{"author_uuid":"a3e78b09-23ae-44fd-9939-865f688894f5","post_uuid":"2ef8f342-6a5c-4e7c-b39f-5d688f0fce10","text":"text"}');

        $user = new User(
            new UUID('a3e78b09-23ae-44fd-9939-865f688894f5'),
            'username',
            new Name('name', 'surname'),
        );

        $commentsRepository = $this->commentsRepository([]);
        $postsRepository = $this->postsRepository([
            new Post(
                new UUID('2ef8f342-6a5c-4e7c-b39f-5d688f0fce10'),
                $user,
                'title',
                'text'
            )
        ]);
        $usersRepository = $this->usersRepository([
            $user
        ]);

        $action = new \Akseonov\Php2\http\Actions\Comments\CreateComment(
            $commentsRepository,
            $postsRepository,
            $usersRepository,
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