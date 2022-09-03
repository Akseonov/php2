<?php

namespace Akseonov\Php2\Blog\Commands;

use Akseonov\Php2\Blog\Comment;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\ArgumentsException;
use Akseonov\Php2\Exceptions\InvalidArgumentException;

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

        $this->repositories['comments_repository']->save(new Comment(
            UUID::random(),
            $post,
            $userComment,
            $arguments->get('text')
        ));
    }
}