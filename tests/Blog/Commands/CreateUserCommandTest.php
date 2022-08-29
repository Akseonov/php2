<?php

namespace Akseonov\Php2\UnitTests\Blog\Commands;

use Akseonov\Php2\Blog\Commands\Arguments;
use Akseonov\Php2\Blog\Commands\CreateUserCommand;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\ArgumentsException;
use Akseonov\Php2\Exceptions\CommandException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\Person\Name;
use Akseonov\Php2\UnitTests\DummyLogger;
use PHPUnit\Framework\TestCase;

class CreateUserCommandTest extends TestCase
{
    private function makeUsersRepositoryWithNotFoundException(): UsersRepositoryInterface
    {
        return new class implements UsersRepositoryInterface
        {
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

    private function makeUserRepositoryWithUserObjectInReturn(): UsersRepositoryInterface
    {
        return new class implements UsersRepositoryInterface
        {
            public function save(User $user): void
            {

            }

            public function get(UUID $uuid): User
            {
                return new User(UUID::random(), "Ivan", '12345', new Name("Ivan", "Nikitin"));
            }

            public function getByUsername(string $username): User
            {
                return new User(UUID::random(), "Ivan", '12345', new Name("Ivan", "Nikitin"));
            }
        };
    }

    /**
     * @throws ArgumentsException
     */
    public function testItThrowsAnExceptionWhenUserAlreadyExists(): void
    {
        $command = new CreateUserCommand(
            $this->makeUserRepositoryWithUserObjectInReturn(),
            new DummyLogger()
        );

        $this->expectException(CommandException::class);
        $this->expectExceptionMessage('User already exists: Ivan');

        $command->handle(new Arguments([
            'username' => 'Ivan',
            'password' => '12345',
        ]));
    }

    /**
     * @throws CommandException
     */
    public function testItRequiresUsername(): void
    {
        $command = new CreateUserCommand(
            $this->makeUsersRepositoryWithNotFoundException(),
            new DummyLogger()
        );

        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: username');

        $command->handle(new Arguments([]));
    }

    /**
     * @throws CommandException
     */
    public function testItRequiresPassword(): void
    {
        $command = new CreateUserCommand(
            $this->makeUsersRepositoryWithNotFoundException(),
            new DummyLogger()
        );

        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: password');

        $command->handle(new Arguments([
            'username' => 'Ivan',
        ]));
    }

    /**
     * @throws CommandException
     */
    public function testItRequiresLastName(): void
    {
        $command = new CreateUserCommand(
            $this->makeUsersRepositoryWithNotFoundException(),
            new DummyLogger()
        );

        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: last_name');

        $command->handle(new Arguments([
            'username' => 'Ivan',
            'password' => '12345',
            'first_name' => 'Ivan',
        ]));
    }

    /**
     * @throws CommandException
     */
    public function testItRequiresFirstName(): void
    {
        $command = new CreateUserCommand(
            $this->makeUsersRepositoryWithNotFoundException(),
            new DummyLogger()
        );

        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: first_name');

        $command->handle(new Arguments([
            'username' => 'Ivan',
            'password' => '12345',
        ]));
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

        $command = new CreateUserCommand($usersRepository, new DummyLogger());

        $command->handle(new Arguments([
            'username' => 'Ivan',
            'password' => '12345',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
        ]));

        $this->assertTrue($usersRepository->wasCalled());
    }
}