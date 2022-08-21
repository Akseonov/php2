<?php

namespace Akseonov\Php2\Blog;

class PostLike
{
    public function __construct(
        private UUID $uuid,
        private Post $post,
        private User $user
    )
    {
    }

    public function __toString(): string
    {
        return $this->user->getUsername() . ' поставил лайк: ' . $this->post->getTitle();
    }

    /**
     * @return UUID
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
}