<?php

namespace Akseonov\Php2\http\Auth;

use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\AuthException;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\Request;

class JsonBodyUuidIdentification implements IdentificationInterface
{
    public function __construct(
        private readonly UsersRepositoryInterface $usersRepository
    ) {
    }

    /**
     * @throws AuthException
     */
    public function user(Request $request): User
    {
        try {
            $userUuid = new UUID($request->jsonBodyField('user_uuid'));
        } catch (HttpException | InvalidArgumentException $exception) {
            throw new AuthException($exception->getMessage());
        }

        try {
            return $this->usersRepository->get($userUuid);
        } catch (UserNotFoundException $exception) {
            throw new AuthException($exception->getMessage());
        }
    }
}