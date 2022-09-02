<?php

namespace Akseonov\Php2\Blog\Repositories\UsersRepository;

use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Psr\Log\LoggerInterface;
use Akseonov\Php2\Exceptions\{UserNotFoundException};
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Person\Name;

class SqliteUsersRepository implements UsersRepositoryInterface
{
    public function __construct(
        private readonly \PDO            $connection,
        private readonly LoggerInterface $logger,
    )
    {
    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    private function getUser(\PDOStatement $statement, string $userInfo): User
    {
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        if ($result === false) {
            $this->logger->warning("Cannot get user: $userInfo");
            throw new UserNotFoundException(
                "Cannot get user: $userInfo"
            );
        }

        $this->logger->info("Finish get user {$result['username']}");

        return new User(
            new UUID($result['uuid']),
            $result['username'],
            $result['password'],
            new Name($result['first_name'], $result['last_name'])
        );
    }

    public function save(User $user): void
    {
        $this->logger->info('Start save user');

        $statement = $this->connection->prepare(
            'INSERT INTO users (uuid, username, password, first_name, last_name)
            VALUES (:uuid, :username, :password, :first_name, :last_name)
            ON CONFLICT (uuid) DO UPDATE SET
                first_name = :first_name,
                last_name = :last_name'
        );

        $statement->execute([
            ':first_name' => $user->getName()->getFirstName(),
            ':last_name' => $user->getName()->getLastName(),
            ':uuid' => (string)$user->getUuid(),
            ':username' => $user->getUsername(),
            ':password' => $user->getPassword(),
        ]);

        $this->logger->info("Finish save user {$user->getUsername()}");
    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function get(UUID $uuid): User
    {
        $this->logger->info('Start get user by uuid');

        $statement = $this->connection->prepare(
            'SELECT * FROM users WHERE uuid = :uuid'
        );

        $statement->execute([
            ':uuid' => (string)$uuid,
        ]);

        return $this->getUser($statement, $uuid);
    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function getByUsername(string $username): User
    {
        $this->logger->info('Start get user by username');

        $statement = $this->connection->prepare(
            'SELECT * FROM users WHERE username = :username'
        );

        $statement->execute([
            ':username' => $username,
        ]);

        return $this->getUser($statement, $username);
    }
}