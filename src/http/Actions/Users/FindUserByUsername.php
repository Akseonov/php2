<?php

namespace Akseonov\Php2\http\Actions\Users;

use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\Actions\ActionInterface;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\Response;
use Akseonov\Php2\http\SuccessfulResponse;
use Psr\Log\LoggerInterface;

class FindUserByUsername implements ActionInterface
{
    public function __construct(
        private readonly UsersRepositoryInterface $usersRepository,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function handle(Request $request): Response
    {
        $this->logger->info('FindUserByUsername action start');

        try {
            $username = $request->query('username');
        } catch (HttpException $exception) {
            $this->logger->warning($exception->getMessage());
            return new ErrorResponse($exception->getMessage());
        }
        try {
            $user = $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException $exception) {
            $this->logger->warning($exception->getMessage());
            return new ErrorResponse($exception->getMessage());
        }

        $this->logger->info("FindUserByUsername action find user: $username");

        return new SuccessfulResponse([
            'uuid' => $user->getUuid(),
            'username' => $user->getUsername(),
            'password' => $user->getPassword(),
            'first_name' => $user->getName()->getFirstName(),
            'last_name' => $user->getName()->getLastName()
        ]);
    }
}