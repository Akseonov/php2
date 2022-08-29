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
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;

class SqlitePostLikesRepository implements PostLikesRepositoryInterface
{
    public function __construct(
        private readonly PDO            $connection,
        private readonly LoggerInterface $logger,
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
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (empty($result)) {
            $this->logger->warning("Cannot get likes for post: $likesInfo");
            throw new LikesPostNotFoundException(
                "Cannot get likes for post: $likesInfo"
            );
        }

        $userRepository = new SqliteUsersRepository(
            $this->connection,
            $this->logger,
        );

        $postRepository = new SqlitePostsRepository(
            $this->connection,
            $this->logger,
        );

        $this->logger->info("Finish get post likes");

        return array_map(function($like) use($userRepository, $postRepository): PostLike
        {
            $userResult = $userRepository->get(new UUID($like['user_uuid']));
            $postResult = $postRepository->get(new UUID($like['post_uuid']));

            return new PostLike(
                new UUID($like['uuid']),
                $postResult,
                $userResult,
            );
        }, $result);
    }

    public function save(PostLike $postLike): void
    {
        $this->logger->info('Start save post like');

        $statement = $this->connection->prepare(
            'INSERT INTO likes_post (uuid, user_uuid, post_uuid)
            VALUES (:uuid, :user_uuid, :post_uuid)'
        );
        $statement->execute([
            ':uuid' => $postLike->getUuid(),
            ':user_uuid' => $postLike->getUser()->getUuid(),
            ':post_uuid' => $postLike->getPost()->getUuid(),
        ]);

        $this->logger->info("Finish save post like: {$postLike->getUuid()}");
    }

    /**
     * @throws PostNotFoundException
     * @throws InvalidArgumentException
     * @throws LikesPostNotFoundException
     * @throws UserNotFoundException
     */
    public function getByPostUuid(UUID $uuid): array
    {
        $this->logger->info('Start get post likes by post uuid');

        $statementLikes = $this->connection->prepare(
            'SELECT * FROM likes_post WHERE likes_post.post_uuid = :uuid'
        );

        $statementLikes->execute([
            ':uuid' => (string)$uuid,
        ]);

        return $this->getLikes($statementLikes, $uuid);
    }
}