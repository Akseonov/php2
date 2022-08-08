<?php

namespace Akseonov\Php2\Repositories;

use Akseonov\Php2\Comments\Comment;
use Akseonov\Php2\Exeptions\CommentNotFoundException;

class InMemoryCommentsRepository
{
    private array $comments = [];

    public function save(Comment $comment): void
    {
        $this->comments[] = $comment;
    }

    public function get(int $id): Comment
    {
        foreach ($this->comments as $comment) {
            if ($comment->id() === $id) {
                return $comment;
            }
        }
        throw new CommentNotFoundException("Post not found: $id");
    }
}