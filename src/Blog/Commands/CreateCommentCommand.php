<?php

namespace Akseonov\Php2\Blog\Commands;

use Akseonov\Php2\Blog\Comment;
use Akseonov\Php2\Blog\Exceptions\ArgumentsException;
use Akseonov\Php2\Blog\Exceptions\CommandException;
use Akseonov\Php2\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use Akseonov\Php2\Blog\UUID;

class CreateCommentCommand
{
    public function __construct(
        private CommentsRepositoryInterface $commentsRepository
    )
    {

    }

    /**
     * @throws ArgumentsException
     */
    public function handle(Arguments $arguments): void
    {
        // Сохраняем пользователя в репозиторий
        $this->commentsRepository->save(new Comment(
            UUID::random(),
            UUID::random(),
            UUID::random(),
            $arguments->get('text')
        ));
    }
}