<?php

namespace Akseonov\Php2\UnitTests\Blog\Commands;

use Akseonov\Php2\Blog\Commands\Arguments;
use Akseonov\Php2\Blog\Commands\CreatePostCommand;
use Akseonov\Php2\Blog\Exceptions\ArgumentsException;
use Akseonov\Php2\Blog\Exceptions\CommandException;
use Akseonov\Php2\Blog\Exceptions\PostNotFoundException;
use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostsRepositoryInterface;
use Akseonov\Php2\Blog\UUID;
use PHPUnit\Framework\TestCase;

class CreatePostCommandTest extends TestCase
{
    private function makePostRepositoryWithNotFoundException(): PostsRepositoryInterface
    {
        return new class implements PostsRepositoryInterface
        {
            public function save(Post $post): void
            {

            }

            public function get(UUID $uuid): Post
            {
                throw new PostNotFoundException("Not found");
            }

            public function getByTitle(string $title): Post
            {
                throw new PostNotFoundException("Not found");
            }
        };
    }

    private function makePostRepositoryWithPostObjectInReturn(): PostsRepositoryInterface
    {
        return new class implements PostsRepositoryInterface
        {
            public function save(Post $post): void
            {

            }

            public function get(UUID $uuid): Post
            {
                return new Post(
                    new UUID('123e4567-e89b-12d3-a456-426614174000'),
                    new UUID('9de6281b-6fa3-427b-b071-4ca519586e74'),
                    'Мой дом',
                    'Это мой рандомнй текст'
                );
            }

            public function getByTitle(string $title): Post
            {
                return new Post(
                    new UUID('123e4567-e89b-12d3-a456-426614174000'),
                    new UUID('9de6281b-6fa3-427b-b071-4ca519586e74'),
                    'Мой дом',
                    'Это мой рандомнй текст'
                );
            }
        };
    }

    /**
     * @throws ArgumentsException
     */
    public function testItThrowsAnExceptionWhenPostAlreadyExists(): void
    {
        $command = new CreatePostCommand($this->makePostRepositoryWithPostObjectInReturn());

        $this->expectException(CommandException::class);
        $this->expectExceptionMessage('Post already exists: Мой дом');

        $command->handle(new Arguments([
            'title' => 'Мой дом',
        ]));
    }

    /**
     * @throws CommandException
     */
    public function testItRequiresTitle(): void
    {
        $command = new CreatePostCommand($this->makePostRepositoryWithNotFoundException());

        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: title');

        $command->handle(new Arguments([

        ]));
    }

    /**
     * @throws CommandException
     */
    public function testItRequiresText(): void
    {
        $command = new CreatePostCommand($this->makePostRepositoryWithNotFoundException());

        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: text');

        $command->handle(new Arguments([
            'title' => 'Мой дом',
        ]));
    }

    /**
     * @throws CommandException
     * @throws ArgumentsException
     */
    public function testItSavesUserToRepository(): void
    {
        $usersRepository = new class implements PostsRepositoryInterface {

            private bool $called = false;

            public function save(Post $post): void
            {
                $this->called = true;
            }
            public function get(UUID $uuid): Post
            {
                throw new PostNotFoundException("Not found");
            }

            public function getByTitle(string $title): Post
            {
                throw new PostNotFoundException("Not found");
            }

            public function wasCalled(): bool
            {
                return $this->called;
            }
        };

        $command = new CreatePostCommand($usersRepository);

        $command->handle(new Arguments([
            'title' => 'Мой дом',
            'text' => 'Это мой рандомнй текст',
        ]));

        $this->assertTrue($usersRepository->wasCalled());
    }
}