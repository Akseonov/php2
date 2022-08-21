<?php

namespace Akseonov\Php2\Blog\Commands;

use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\ArgumentsException;
use Akseonov\Php2\Exceptions\CommandException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\Person\Name;

class CreateUserCommand
{
    public function __construct(
        private UsersRepositoryInterface $usersRepository
    )
    {

    }

    /**
     * @throws CommandException
     * @throws ArgumentsException
     */
    public function handle(Arguments $arguments): void
    {
        $username = $arguments->get('username');

        if ($this->userExists($username)) {
            throw new CommandException("User already exists: $username");
        }
        $this->usersRepository->save(new User(
            UUID::random(),
            $username,
            new Name($arguments->get('first_name'), $arguments->get('last_name'))
        ));
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