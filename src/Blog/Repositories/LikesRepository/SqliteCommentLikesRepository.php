<?php

namespace Akseonov\Php2\Blog\Repositories\LikesRepository;

use Akseonov\Php2\Blog\CommentLike;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentLikesRepositoryInterface;
use Akseonov\Php2\Blog\UUID;
use PDOStatement;

class SqliteCommentLikesRepository implements CommentLikesRepositoryInterface
{
    public function __construct(
//        private \PDO $connection,
    )
    {
    }

    public function getLikes(PDOStatement $statement, string $likesInfo): array
    {
        return [
//            new CommentLike(),
//            new CommentLike()
        ];
    }

    public function save(CommentLike $commentLike): void
    {
    }

    public function getByCommentUuid(UUID $uuid): array
    {
        return [];
    }
}