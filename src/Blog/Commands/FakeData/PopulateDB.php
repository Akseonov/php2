<?php

namespace Akseonov\Php2\Blog\Commands\FakeData;

use Akseonov\Php2\Blog\Comment;
use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentsRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostsRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Person\Name;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Faker\Generator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateDB extends Command
{
    public function __construct(
        private readonly Generator                   $faker,
        private readonly UsersRepositoryInterface    $usersRepository,
        private readonly PostsRepositoryInterface    $postsRepository,
        private readonly CommentsRepositoryInterface $commentsRepository,
        private readonly LoggerInterface             $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('fake-data:populate-db')
            ->setDescription('Populates DB with fake data')
            ->addOption(
                'users-number',
                'un',
                InputOption::VALUE_OPTIONAL,
                'create number of users',
            )
            ->addOption(
                'posts-number',
                'pn',
                InputOption::VALUE_OPTIONAL,
                'create number of posts',
            )
            ->addOption(
                'comments-number',
                'cn',
                InputOption::VALUE_OPTIONAL,
                'create number of comments',
            );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $users = [];
        $posts = [];
        $usersNumber = $input->getOption('users-number');
        $postsNumber = $input->getOption('posts-number');
        $commentsNumber = $input->getOption('comments-number');

        if ($usersNumber > 1000 || $usersNumber <= 0) {
            $this->logger->warning('Enter the number of users from 1 to 1000');
            $output->writeln('Enter the number of users from 1 to 1000');
            return Command::SUCCESS;
        }

        if ($postsNumber > 1000 || $postsNumber <= 0) {
            $this->logger->warning('Enter the number of posts from 1 to 1000');
            $output->writeln('Enter the number of posts from 1 to 1000');
            return Command::SUCCESS;
        }

        if ($commentsNumber > 1000 || $commentsNumber <= 0) {
            $this->logger->warning('Enter the number of comments from 1 to 1000');
            $output->writeln('Enter the number of comments from 1 to 1000');
            return Command::SUCCESS;
        }

        for ($i = 0; $i < $usersNumber; $i++) {
            $user = $this->createFakeUser();
            $users[] = $user;

            $this->logger->info('User created: ' . $user->getUsername());
            $output->writeln('User created: ' . $user->getUsername());
        }

        foreach ($users as $user) {
            for ($i = 0; $i < $postsNumber; $i++) {
                $post = $this->createFakePost($user);
                $posts[] = $post;

                $this->logger->info('Post created: ' . $post->getTitle());
                $output->writeln('Post created: ' . $post->getTitle());
            }
        }

        foreach ($users as $user) {
            foreach ($posts as $post) {
                for ($i = 0; $i < $commentsNumber; $i++) {
                    $comment = $this->createFakeComments($user, $post);

                    $this->logger->info('Comment created: ' . $comment->getUuid());
                    $output->writeln('Comment created: ' . $comment->getUuid());
                }
            }
        }

        return Command::SUCCESS;
    }

    private function createFakeUser(): User
    {
        $user = User::createForm(
            $this->faker->userName,
            $this->faker->password,
            new Name(
                $this->faker->firstName,
                $this->faker->lastName
            )
        );

        $this->usersRepository->save($user);
        return $user;
    }

    private function createFakePost(User $author): Post
    {
        $post = new Post(
            UUID::random(),
            $author,
            $this->faker->realText(25, 2),
            $this->faker->realText
        );

        $this->postsRepository->save($post);
        return $post;
    }

    private function createFakeComments(User $author, Post $post): Comment
    {
        $comment = new Comment(
            UUID::random(),
            $post,
            $author,
            $this->faker->realText(100, 2),
        );

        $this->commentsRepository->save($comment);
        return $comment;
    }
}