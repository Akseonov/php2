<?php

namespace Akseonov\Php2\Blog\Commands\Users;

use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\Person\Name;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUser extends Command
{
    public function __construct(
        private readonly UsersRepositoryInterface $usersRepository,
        private readonly LoggerInterface $logger,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('users:create')
            ->setDescription('Creates new user')
            ->addArgument(
                'first_name',
                InputArgument::REQUIRED,
                'First name'
            )
            ->addArgument(
                'last_name',
                InputArgument::REQUIRED,
                'Last name')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'Username')
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'Password');
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int
    {
        $this->logger->info('Create user command started');
        $output->writeln('Create user command started');

        $username = $input->getArgument('username');

        if ($this->userExists($username)) {
            $this->logger->warning("User already exists: $username");
            $output->writeln("User already exists: $username");
            return Command::FAILURE;
        }

        $user = User::createForm(
            $username,
            $input->getArgument('password'),
            new Name(
                $input->getArgument('first_name'),
                $input->getArgument('last_name')
            )
        );

        $this->usersRepository->save($user);

        $this->logger->info('User created: ' . $user->getUuid());
        $output->writeln('User created: ' . $user->getUuid());

        return Command::SUCCESS;
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