<?php

namespace Akseonov\Php2\Blog;

class Comment
{
    public function __construct(
        private UUID $uuid,
        private Post $post,
        private User $user,
        private string $text
    )
    {
    }

    public function __toString(): string
    {
        return $this->user->getUsername() . ' пишет комментарий: ' . $this->text . PHP_EOL;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return (string)$this->uuid;
    }

    /**
     * @return Post
     */
    public function getPost(): Post
    {
        return $this->post;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }
}