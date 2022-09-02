<?php

namespace Akseonov\Php2\Blog\Commands\Posts;

use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostsRepositoryInterface;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DeletePost extends Command
{
    public function __construct(
        private readonly PostsRepositoryInterface $postsRepository,
        private readonly LoggerInterface $logger,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('posts:delete')
            ->setDescription('Deletes a post')
            ->addArgument(
                'uuid',
                InputArgument::REQUIRED,
                'UUID of a post to delete'
            );
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $this->logger->info('Delete post command started');
        $question = new ConfirmationQuestion(
            'Delete post [Y/n]? ',
            false
        );

        if (!$this->getHelper('question')
            ->ask($input, $output, $question)
        ) {
            return Command::SUCCESS;
        }

        $uuid = new UUID($input->getArgument('uuid'));

        $this->postsRepository->delete($uuid);

        $output->writeln("Post $uuid deleted");
        $this->logger->info("Post $uuid deleted");

        return Command::SUCCESS;
    }
}