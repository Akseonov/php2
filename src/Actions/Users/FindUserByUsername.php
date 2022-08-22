<?php

namespace Akseonov\Php2\Actions\Users;

use Akseonov\Php2\Actions\ActionInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\Response;
use Akseonov\Php2\http\SuccessfulResponse;

class FindUserByUsername implements ActionInterface
{
    public function __construct(
        private readonly UsersRepositoryInterface $usersRepository
    )
    {
    }

    public function handle(Request $request): Response
    {
        try {
            $username = $request->query('username');
        } catch (HttpException $exception) {
            return new ErrorResponse($exception->getMessage());
        }
        try {
            $user = $this->usersRepository->getByUsername($username);
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