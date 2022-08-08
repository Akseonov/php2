<?php

use Akseonov\Php2\Users\User;
use Akseonov\Php2\Posts\Post;
use Akseonov\Php2\Comments\Comment;
use Akseonov\Php2\Repositories\InMemoryPostsRepository;
use Akseonov\Php2\Repositories\InMemoryCommentsRepository;
use Faker\Provider\ru_RU\{Person,Text};

require_once __DIR__ . "/vendor/autoload.php";

$faker = Faker\Factory::create('Ru_RU');
$fakerTextRu = new Text($faker);

switch ($argv[1]) {
    case 'user':
        echo new User(1, $faker->firstName, $faker->lastName);
        break;
    case 'post':
        echo new Post(1, 1, $fakerTextRu->realTextBetween(), $fakerTextRu->realText());
        break;
    case 'comment':
        echo new Comment(1, 1, 1, $fakerTextRu->realText());
        break;
}


