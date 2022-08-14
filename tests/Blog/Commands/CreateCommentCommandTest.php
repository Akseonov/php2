<?php

namespace Akseonov\Php2\UnitTests\Blog\Commands;

use Akseonov\Php2\Blog\Commands\Arguments;
use Akseonov\Php2\Blog\Commands\CreateCommentCommand;
use Akseonov\Php2\Blog\Comment;
use Akseonov\Php2\Blog\Exceptions\ArgumentsException;
use Akseonov\Php2\Blog\Exceptions\CommandException;
use Akseonov\Php2\Blog\Exceptions\CommentNotFoundException;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentsRepositoryInterface;
use Akseonov\Php2\Blog\UUID;
use PHPUnit\Framework\TestCase;

class CreateCommentCommandTest extends TestCase
{
    private function makeCommentRepositoryWithNotFoundException(): CommentsRepositoryInterface
    {
        return new class implements CommentsRepositoryInterface
        {
            public function save(Comment $comment): void
            {

            }

            public function get(UUID $uuid): Comment
            {
                throw new CommentNotFoundException("Not found");
            }
        };
    }

    public function testItRequiresText(): void
    {
        $command = new CreateCommentCommand($this->makeCommentRepositoryWithNotFoundException());

        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: text');

        $command->handle(new Arguments([

        ]));
    }

    public function testItSavesCommentToRepository(): void
    {
        $usersRepository = new class implements CommentsRepositoryInterface {

            private bool $called = false;

            public function save(Comment $comment): void
            {
                $this->called = true;
            }
            public function get(UUID $uuid): Comment
            {
                throw new CommentNotFoundException("Not found");
            }

            public function wasCalled(): bool
            {
                return $this->called;
            }
        };

        $command = new CreateCommentCommand($usersRepository);

        $command->handle(new Arguments([
            'title' => 'Мой дом',
            'text' => 'Это мой рандомнй текст',
        ]));

        $this->assertTrue($usersRepository->wasCalled());
    }
}