<?php

namespace Akseonov\Php2\Blog\Repositories\CommentsRepository;

use Akseonov\Php2\Blog\Comment;
use Akseonov\Php2\Blog\UUID;

interface CommentsRepositoryInterface
{
    public function save(Comment $comment): void;
    public function get(UUID $uuid): Comment;
}