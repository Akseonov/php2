<?php

namespace Akseonov\Php2\Blog\Repositories\RepositoryInterfaces;

use Akseonov\Php2\Blog\CommentLike;
use Akseonov\Php2\Blog\UUID;

interface CommentLikesRepositoryInterface
{
    public function save(CommentLike $commentLike): void;
    public function getByCommentUuid(UUID $uuid): array;
}