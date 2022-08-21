<?php

namespace Akseonov\Php2\UnitTests\Actions\Posts;

use Akseonov\Php2\Actions\Posts\FindPostByTitle;
use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostsRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\SuccessfulResponse;
use Akseonov\Php2\Person\Name;
use PHPUnit\Framework\TestCase;
use JsonException;

class FindUserByTitleActionTest extends TestCase
{
    private function postsRepository(array $posts): PostsRepositoryInterface
    {
        return new class($posts) implements PostsRepositoryInterface
        {
            public function __construct(
                private array $posts
            )
            {
            }

            public function save(Post $post): void
            {
            }

            public function get(UUID $uuid): Post
            {
                throw new PostNotFoundException('Not found');
            }

            public function getByTitle(string $title): Post
            {
                foreach ($this->posts as $post) {
                    if ($post instanceof Post && $title === $post->getTitle()) {
                        return $post;
                    }
                }
                throw new PostNotFoundException('Not found');
            }

            public function delete(UUID $uuid): void
            {
            }
        };
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfNoTitledProvided(): void
    {

        $request = new Request([], [], '');

        $repository = $this->postsRepository([]);

        $action = new FindPostByTitle($repository);

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such query param in the request: title"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfPostNotFound(): void
    {
        $request = new Request([
            'title' => 'Привет'
        ], [], '');

        $repository = $this->postsRepository([]);

        $action = new FindPostByTitle($repository);

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
        $request = new Request([
            'title' => 'title'
        ], [], '');

        $user = new User(
            new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
            'username',
            new Name('name', 'surname')
        );

        $repository = $this->postsRepository([
            new Post(
                new UUID('a3e78b09-23ae-44fd-9939-865f688894f5'),
                $user,
                'title',
                'text'
            )
        ]);

        $action = new FindPostByTitle($repository);

        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $this->expectOutputString('{"success":true,"data":{"uuid":"a3e78b09-23ae-44fd-9939-865f688894f5","user":{"uuid":"10373537-0805-4d7a-830e-22b481b4859c","username":"username","first_name":"name","last_name":"surname"},"title":"title","text":"text"}}');

        $response->send();
    }
}