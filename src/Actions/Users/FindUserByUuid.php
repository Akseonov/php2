<?php

namespace Akseonov\Php2\Actions\Users;

use Akseonov\Php2\Actions\ActionInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\Response;
use Akseonov\Php2\http\SuccessfulResponse;

class FindUserByUuid implements ActionInterface
{
    public function __construct(
        private readonly UsersRepositoryInterface $usersRepository
    )
    {
    }

    public function handle(Request $request): Response
    {
        try {
            $uuid = $request->query('uuid');
        } catch (HttpException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $uuidClass = new UUID($uuid);
        } catch (InvalidArgumentException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $user = $this->usersRepository->get($uuidClass);
        } catch (UserNotFoundException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        return new SuccessfulResponse([
            'uuid' => $user->getUuid(),
            'username' => $user->getUsername(),
            'first_name' => $user->getName()->getFirstName(),
            'last_name' => $user->getName()->getLastName()
        ]);
    }
}