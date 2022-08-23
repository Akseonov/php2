<?php

namespace Akseonov\Php2\UnitTests\http\Actions\Likes;

use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\PostLike;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostLikesRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostsRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\LikesPostNotFoundException;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\Actions\Likes\CreatePostLike;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\SuccessfulResponse;
use Akseonov\Php2\Person\Name;
use Akseonov\Php2\UnitTests\DummyLogger;
use JsonException;
use PHPUnit\Framework\TestCase;

class CreatePostLikeActionTest extends TestCase
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

            private bool $called = false;


            public function save(PostLike $postLike): void
            {
                $this->called = true;
            }

            public function getCalled(): bool
            {
                return $this->called;
            }

            public function getByPostUuid(UUID $uuid): array
            {
                $likes = [];

                foreach ($this->likes as $like) {
                    if ($like instanceof PostLike && (string)$uuid === $like->getPost()->getUuid()) {
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

        $postLikesRepository = $this->postLikesRepository([]);
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([]);

        $action = new CreatePostLike(
            $postLikesRepository,
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

        $postLikesRepository = $this->postLikesRepository([]);
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([]);

        $action = new CreatePostLike(
            $postLikesRepository,
            $postsRepository,
            $usersRepository,
            new DummyLogger()
        );

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

        $postLikesRepository = $this->postLikesRepository([]);
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([]);

        $action = new CreatePostLike(
            $postLikesRepository,
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
    public function testItReturnsErrorResponseIfUserNotFound(): void
    {
        $request = new Request([], [], '{"user_uuid":"a3e78b09-23ae-44fd-9939-865f688894f5","post_uuid":"2ef8f342-6a5c-4e7c-b39f-5d688f0fce10"}');

        $postLikesRepository = $this->postLikesRepository([]);
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([]);

        $action = new CreatePostLike(
            $postLikesRepository,
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
        $request = new Request([], [], '{"user_uuid":"a3e78b09-23ae-44fd-9939-865f688894f5","post_uuid":"2ef8f342-6a5c-4e7c-b39f-5d688f0fce10","text":"text"}');

        $postLikesRepository = $this->postLikesRepository([]);
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([
            new User(
                new UUID('a3e78b09-23ae-44fd-9939-865f688894f5'),
                'username',
                new Name('name', 'surname'),
            )
        ]);

        $action = new CreatePostLike(
            $postLikesRepository,
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
    public function testItReturnsErrorResponseIfPostLikeAlreadyExist(): void
    {
        $request = new Request([], [], '{"user_uuid":"a3e78b09-23ae-44fd-9939-865f688894f5","post_uuid":"2ef8f342-6a5c-4e7c-b39f-5d688f0fce10"}');

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

        $postLikesRepository = $this->postLikesRepository([
            new PostLike(
                new UUID('aa081104-db69-44db-af66-b7e43090596f'),
                $post,
                $user
            )
        ]);

        $postsRepository = $this->postsRepository([
            $post
        ]);
        $usersRepository = $this->usersRepository([
            $user
        ]);

        $action = new CreatePostLike(
            $postLikesRepository,
            $postsRepository,
            $usersRepository,
            new DummyLogger()
        );

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Post like already exists: a3e78b09-23ae-44fd-9939-865f688894f5, 2ef8f342-6a5c-4e7c-b39f-5d688f0fce10"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsSuccessfulResponse(): void
    {
        $request = new Request([], [], '{"user_uuid":"cc95e109-3a63-4f39-8183-c3d3fc861f16","post_uuid":"2ef8f342-6a5c-4e7c-b39f-5d688f0fce10"}');

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

        $postLikesRepository = $this->postLikesRepository([
            new PostLike(
                new UUID('aa081104-db69-44db-af66-b7e43090596f'),
                $post,
                $user
            )
        ]);
        $postsRepository = $this->postsRepository([
            $post
        ]);
        $usersRepository = $this->usersRepository([
            $user,
            new User(
                new UUID('cc95e109-3a63-4f39-8183-c3d3fc861f16'),
                'admin',
                new Name('pat', 'mat')
            )
        ]);

        $action = new CreatePostLike(
            $postLikesRepository,
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
            $dataDecode['data']['uuid'] = "351739ab-fc33-49ae-a62d-b606b7038c87";
            return json_encode(
                $dataDecode,
                JSON_THROW_ON_ERROR
            );
        });

        $response->send();
    }
}