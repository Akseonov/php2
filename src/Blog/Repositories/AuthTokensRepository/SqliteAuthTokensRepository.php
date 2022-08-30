<?php

namespace Akseonov\Php2\Blog\Repositories\AuthTokensRepository;

use Akseonov\Php2\Blog\AuthToken;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\AuthTokensRepositoryInterface;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\AuthTokenNotFoundException;
use Akseonov\Php2\Exceptions\AuthTokensRepositoryException;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use PDO;
use PDOException;
use Psr\Log\LoggerInterface;

class SqliteAuthTokensRepository implements AuthTokensRepositoryInterface
{
    public function __construct(
        private readonly PDO $connection,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws AuthTokensRepositoryException
     */
    public function save(AuthToken $authToken): void
    {
        $this->logger->info('Start save user token');

        $query = <<<'SQL'
            INSERT INTO tokens (token, user_uuid, expires_on) 
            VALUES (:token, :user_uuid, :expires_on)
            ON CONFLICT (token) DO UPDATE SET
            expires_on = :expires_on
SQL;
        try {
            $statement = $this->connection->prepare($query);
            $statement->execute([
                ':token' => $authToken->getToken(),
                ':user_uuid' => (string)$authToken->getUserUuid(),
                ':expires_on' => $authToken->getExpiresOn()
                    ->format(DateTimeInterface::ATOM),
            ]);

            $this->logger->info("Finish save user token: {$authToken->getUserUuid()}");
        } catch (PDOException $exception) {
            $this->logger->warning($exception->getMessage() . (int)$exception->getCode() . $exception);
            throw new AuthTokensRepositoryException(
                $exception->getMessage(), (int)$exception->getCode(), $exception
            );
        }
    }

    /**
     * @throws AuthTokensRepositoryException
     * @throws AuthTokenNotFoundException
     */
    public function get(string $token): AuthToken
    {
        $this->logger->info('Start get user token');

        try {
            $statement = $this->connection->prepare(
                'SELECT * FROM tokens WHERE token = ?'
            );
            $statement->execute([$token]);
            $result = $statement->fetch(PDO::FETCH_ASSOC);
//            var_dump($result);
//            die();
        } catch (PDOException $exception) {
            $this->logger->warning($exception->getMessage() . (int)$exception->getCode() . $exception);
            throw new AuthTokensRepositoryException(
                $exception->getMessage(), (int)$exception->getCode(), $exception
            );
        }

        if ($result === false) {
            $this->logger->warning("Cannot find token: $token");
            throw new AuthTokenNotFoundException("Cannot find token: $token");
        }

        try {
            $this->logger->info("Finish get user token: {$token}");

            return new AuthToken(
                $result['token'],
                new UUID($result['user_uuid']),
                new DateTimeImmutable($result['expires_on'])
            );
        } catch (InvalidArgumentException | Exception $exception) {
            $this->logger->warning($exception->getMessage() . (int)$exception->getCode() . $exception);
            throw new AuthTokensRepositoryException(
                $exception->getMessage(), $exception->getCode(), $exception
            );
        }
    }
}