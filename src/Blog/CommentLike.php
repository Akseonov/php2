<?php

namespace Akseonov\Php2\Blog;

class CommentLike
{
    public function __construct(
        private UUID $uuid,
        private Comment $comment,
        private User $user
    )
    {
    }

    public function __toString(): string
    {
        return $this->user->getUsername() . ' поставил лайк: ' . $this->comment->getText();
    }

    /**
     * @return UUID
     */
    public function getUuid(): string
    {
        return (string)$this->uuid;
    }

    /**
     * @return Comment
     */
    public function getComment(): Comment
    {
        return $this->comment;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }


}