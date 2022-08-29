<?php

namespace Akseonov\Php2\http\Auth;

use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Exceptions\AuthException;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\Auth\Interfaces\IdentificationInterface;
use Akseonov\Php2\http\Request;

class JsonBodyUsernameIdentification implements IdentificationInterface
{
    public function __construct(
        private UsersRepositoryInterface $usersRepository
    ) {
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
            return $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException $exception) {
            throw new AuthException($exception->getMessage());
        }
    }
}