<?php

namespace Akseonov\Php2\UnitTests\Blog\Commands\Users;

use Akseonov\Php2\Blog\Commands\Arguments;
use Akseonov\Php2\Blog\Commands\Users\CreateUser;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\Person\Name;
use Akseonov\Php2\UnitTests\DummyLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class CreateUserTest extends TestCase
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

    public function testItRequiresLastName(): void
    {
        $command = new CreateUser(
            $this->makeUserRepositoryWithUserObjectInReturn(),
            new DummyLogger()
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "last_name").');

        $command->run(
            new ArrayInput([
                'username' => 'Ivan',
                'password' => 'some_password',
                'first_name' => 'Ivan',
            ]),

            new NullOutput()
        );
    }

    public function testItRequiresFirstName(): void
    {
        $command = new CreateUser(
            $this->makeUserRepositoryWithUserObjectInReturn(),
            new DummyLogger()
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "first_name").');

        $command->run(
            new ArrayInput([
                'username' => 'Ivan',
                'password' => 'some_password',
                'last_name' => 'Ivanov',
            ]),

            new NullOutput()
        );
    }

    public function testItRequiresUsername(): void
    {
        $command = new CreateUser(
            $this->makeUserRepositoryWithUserObjectInReturn(),
            new DummyLogger()
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "username").');

        $command->run(
            new ArrayInput([
                'password' => 'some_password',
                'first_name' => 'Ivan',
                'last_name' => 'Ivanov',
            ]),

            new NullOutput()
        );
    }

    public function testItRequiresPassword(): void
    {
        $command = new CreateUser(
            $this->makeUserRepositoryWithUserObjectInReturn(),
            new DummyLogger()
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "password").');

        $command->run(
            new ArrayInput([
                'username' => 'Ivan',
                'first_name' => 'Ivan',
                'last_name' => 'Ivanov',
            ]),

            new NullOutput()
        );
    }
}