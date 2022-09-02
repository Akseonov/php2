<?php

namespace Akseonov\Php2\Blog\Commands\Users;

use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Person\Name;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateUser extends Command
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
            ->setName('users:update')
            ->setDescription('Update a user')
            ->addArgument(
                'uuid',
                InputArgument::REQUIRED,
                'UUID of a user to update'
            )
            ->addOption(
                'first-name',
                'f',
                InputOption::VALUE_OPTIONAL,
                'First name',
            )
            ->addOption(
                'last-name',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Last name',
            );
    }

    /**
     * @throws \Akseonov\Php2\Exceptions\InvalidArgumentException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int
    {
        $this->logger->info('Update user command started');

        $firstName = $input->getOption('first-name');
        $lastName = $input->getOption('last-name');

        if (empty($firstName) && empty($lastName)) {
            $this->logger->warning('Nothing to update');
            $output->writeln('Nothing to update');
            return Command::SUCCESS;
        }

        $uuid = new UUID($input->getArgument('uuid'));
        $user = $this->usersRepository->get($uuid);

        $updatedName = new Name(
            firstName: empty($firstName) ? $user->getName()->getFirstName() : $firstName,
            lastName: empty($lastName) ? $user->getName()->getLastName() : $lastName,
        );
        $updateUser = new User(
            uuid: $uuid,
            username: $user->getUsername(),
            password: $user->getPassword(),
            name: $updatedName
        );
        $this->usersRepository->save($updateUser);

        $this->logger->info("Update user: $uuid");
        $output->writeln("Update user: $uuid");

        return Command::SUCCESS;
    }
}