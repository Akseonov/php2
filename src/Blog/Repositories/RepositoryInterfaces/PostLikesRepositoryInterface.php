<?php

namespace Akseonov\Php2\Blog\Repositories\RepositoryInterfaces;

use Akseonov\Php2\Blog\PostLike;
use Akseonov\Php2\Blog\UUID;
use PDOStatement;

interface PostLikesRepositoryInterface
{
    public function save(PostLike $postLike): void;
    public function getByPostUuid(UUID $uuid): array;
}