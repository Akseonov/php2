<?php

namespace Akseonov\Php2\Blog\Commands;

use Akseonov\Php2\Blog\Comment;
use Akseonov\Php2\Blog\Exceptions\ArgumentsException;
use Akseonov\Php2\Blog\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentsRepositoryInterface;
use Akseonov\Php2\Blog\UUID;

class CreateCommentCommand
{
    public function __construct(
        private array $repositories,
    )
    {

    }

    /**
     * @throws ArgumentsException
     * @throws InvalidArgumentException
     */
    public function handle(Arguments $arguments): void
    {
        $post = $this->repositories['posts_repository']->get(
            new UUID($arguments->get('post_uuid'))
        );

        $userComment = $this->repositories['users_repository']->get(
            new UUID($arguments->get('author_uuid'))
        );

        // Сохраняем пользователя в репозиторий
        $this->repositories['comments_repository']->save(new Comment(
            UUID::random(),
            $post,
            $userComment,
            $arguments->get('text')
        ));
    }
}