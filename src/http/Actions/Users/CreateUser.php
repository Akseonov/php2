<?php

namespace Akseonov\Php2\http\Actions\Users;

use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\Actions\ActionInterface;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\Response;
use Akseonov\Php2\http\SuccessfulResponse;
use Akseonov\Php2\Person\Name;
use Psr\Log\LoggerInterface;

class CreateUser implements ActionInterface
{
    public function __construct(
        private readonly UsersRepositoryInterface $usersRepository,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function handle(Request $request): Response
    {
        $this->logger->info('CreateUser action start');

        try {
            $username = $request->jsonBodyField('username');

            if ($this->userExists($username)) {
                $this->logger->warning("User already exists: $username");
                throw new HttpException("User already exists: $username");
            }

            $newUserUuid = UUID::random();

            $user = new User(
                $newUserUuid,
                $username,
                new Name(
                    $request->jsonBodyField('first_name'),
                    $request->jsonBodyField('last_name')
                )
            );
        } catch (HttpException $exception) {
            $this->logger->warning($exception->getMessage());
            return new ErrorResponse($exception->getMessage());
        }

        $this->usersRepository->save($user);
        $this->logger->info("CreateUser action save user: $newUserUuid");

        return new SuccessfulResponse([
            'uuid' => (string)$newUserUuid
        ]);
    }

    private function userExists(string $username): bool
    {
        try {
            $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException) {
            return false;
        }
        return true;
    }
}