<?php

namespace Akseonov\Php2\http\Actions\Users;

use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\Actions\ActionInterface;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\Response;
use Akseonov\Php2\http\SuccessfulResponse;
use Psr\Log\LoggerInterface;

class FindUserByUuid implements ActionInterface
{
    public function __construct(
        private readonly UsersRepositoryInterface $usersRepository,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function handle(Request $request): Response
    {
        $this->logger->info('FindUserByUuid action start');

        try {
            $uuid = $request->query('uuid');
        } catch (HttpException $exception) {
            $this->logger->warning($exception->getMessage());
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $user = $this->usersRepository->get(new UUID($uuid));
        } catch (UserNotFoundException | InvalidArgumentException $exception) {
            $this->logger->warning($exception->getMessage());
            return new ErrorResponse($exception->getMessage());
        }

        $this->logger->info("FindUserByUuid action find user: $uuid");

        return new SuccessfulResponse([
            'uuid' => $user->getUuid(),
            'username' => $user->getUsername(),
            'first_name' => $user->getName()->getFirstName(),
            'last_name' => $user->getName()->getLastName()
        ]);
    }
}