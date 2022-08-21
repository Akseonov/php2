<?php

namespace Akseonov\Php2\Blog\Repositories\LikesRepository;

use Akseonov\Php2\Blog\PostLike;
use Akseonov\Php2\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostLikesRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Exceptions\LikesPostNotFoundException;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use PDOStatement;

class SqlitePostLikesRepository implements PostLikesRepositoryInterface
{
    public function __construct(
        private readonly \PDO $connection,
    )
    {
    }

    /**
     * @throws LikesPostNotFoundException
     * @throws InvalidArgumentException
     * @throws UserNotFoundException
     * @throws PostNotFoundException
     */
    private function getLikes(PDOStatement $statement, string $likesInfo): array
    {
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($result)) {
            throw new LikesPostNotFoundException(
                "Cannot get likes for post: $likesInfo"
            );
        }

        $userRepository = new SqliteUsersRepository(
            $this->connection
        );

        $postRepository = new SqlitePostsRepository(
            $this->connection
        );

        $likes = [];

        foreach ($result as $like) {
            $userResult = $userRepository->get(new UUID($like['user_uuid']));
            $postResult = $postRepository->get(new UUID($like['post_uuid']));

            $likes[] = new PostLike(
                new UUID($like['uuid']),
                $postResult,
                $userResult,
            );
        }

        return $likes;
    }

    public function save(PostLike $postLike): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO likes_post (uuid, user_uuid, post_uuid)
            VALUES (:uuid, :user_uuid, :post_uuid)'
        );
        $statement->execute([
            ':uuid' => $postLike->getUuid(),
            ':user_uuid' => $postLike->getUser()->getUuid(),
            ':post_uuid' => $postLike->getPost()->getUuid(),
        ]);
    }

    /**
     * @throws PostNotFoundException
     * @throws InvalidArgumentException
     * @throws LikesPostNotFoundException
     * @throws UserNotFoundException
     */
    public function getByPostUuid(UUID $uuid): array
    {
        $statementLikes = $this->connection->prepare(
            'SELECT * FROM likes_post WHERE likes_post.post_uuid = :uuid'
        );

        $statementLikes->execute([
            ':uuid' => (string)$uuid,
        ]);

        return $this->getLikes($statementLikes, $uuid);
    }
}