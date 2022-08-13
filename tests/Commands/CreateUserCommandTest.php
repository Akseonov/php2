<?php

namespace Akseonov\Php2\Blog\UnitTests\Commands;

use Akseonov\Php2\Blog\Commands\Arguments;
use Akseonov\Php2\Blog\Commands\CreateUserCommand;
use Akseonov\Php2\Blog\Exceptions\ArgumentsException;
use Akseonov\Php2\Blog\Exceptions\CommandException;
use Akseonov\Php2\Blog\Exceptions\UserNotFoundException;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\UsersRepository\DummyUsersRepository;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use PHPUnit\Framework\TestCase;

class CreateUserCommandTest extends TestCase
{
    /**
     * @throws ArgumentsException
     */
    public function testItThrowsAnExceptionWhenUserAlreadyExists(): void
    {
        $command = new CreateUserCommand(
            new DummyUsersRepository()
        );

        $this->expectException(CommandException::class);
        $this->expectExceptionMessage('User already exists: Ivan');

        $command->handle(new Arguments(['username' => 'Ivan']));
    }

    private function makeUsersRepository(): UsersRepositoryInterface
    {
        return new class implements UsersRepositoryInterface {
            public function save(User $user): void
            {
            }
            public function get(UUID $uuid): User
            {
                throw new UserNotFoundException("Not found");
            }
            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException("Not found");
            }
        };
    }

    /**
     * @throws CommandException
     */
    public function testItRequiresLastName(): void
    {
        $command = new CreateUserCommand($this->makeUsersRepository());
        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: last_name');
        $command->handle(new Arguments([
            'username' => 'Ivan',
            'first_name' => 'Ivan',
        ]));
    }

    /**
     * @throws CommandException
     */
    public function testItRequiresFirstName(): void
    {
        $command = new CreateUserCommand($this->makeUsersRepository());
        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: first_name');
        $command->handle(new Arguments(['username' => 'Ivan']));
    }

    /**
     * @throws CommandException
     * @throws ArgumentsException
     */
    public function testItSavesUserToRepository(): void
    {
        $usersRepository = new class implements UsersRepositoryInterface {

            private bool $called = false;

            public function save(User $user): void
            {
                $this->called = true;
            }
            public function get(UUID $uuid): User
            {
                throw new UserNotFoundException("Not found");
            }
            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException("Not found");
            }

            public function wasCalled(): bool
            {
                return $this->called;
            }
        };

        $command = new CreateUserCommand($usersRepository);

        $command->handle(new Arguments([
            'username' => 'Ivan',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
        ]));

        $this->assertTrue($usersRepository->wasCalled());
    }
}