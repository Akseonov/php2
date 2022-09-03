<?php

namespace Akseonov\Php2\http\Auth;

use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Exceptions\AuthException;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\Auth\Interfaces\PasswordAuthenticationInterface;
use Akseonov\Php2\http\Request;

class PasswordAuthentication implements PasswordAuthenticationInterface
{
    public function __construct(
        private readonly UsersRepositoryInterface $usersRepository
    )
    {
    }

    /**
     * @throws AuthException
     */
    public function user(Request $request): User
    {
        try {
            $username = $request->jsonBodyField('username');
        } catch (HttpException $exception) {
            throw new AuthException($exception->getMessage());
        }
        try {
            $user = $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException $exception) {
            throw new AuthException($exception->getMessage());
        }

        try {
            $password = $request->jsonBodyField('password');
        } catch (HttpException $exception) {
            throw new AuthException($exception->getMessage());
        }

        if (!$user->checkPassword($password)) {
            throw new AuthException('Wrong password');
        }

        return $user;
    }
}