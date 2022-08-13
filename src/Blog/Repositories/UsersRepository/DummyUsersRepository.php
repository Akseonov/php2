<?php

namespace Akseonov\Php2\Blog\Repositories\UsersRepository;

use Akseonov\Php2\Blog\Exceptions\UserNotFoundException;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Person\Name;

class DummyUsersRepository implements UsersRepositoryInterface
{

    public function save(User $user): void
    {
        // TODO: Implement save() method.
    }

    /**
     * @throws UserNotFoundException
     */
    public function get(UUID $uuid): User
    {
        // TODO: Implement get() method.
        throw new UserNotFoundException("Not found");
    }

    public function getByUsername(string $username): User
    {
        // TODO: Implement getByUsername() method.
        return new User(UUID::random(), "user123", new Name("first", "last"));
    }
}