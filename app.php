<?php
//$hero = 'Conan';
//$hero_hp = 10;
//
//$unit = 'monster';
//
//$hero = [
//    'name' => 'Conan',
//    'hp' => 20,
//    'info' => function() {
//        echo 'Привет Conan';
//    }
//];
//
//print_r($hero);
//$hero['info']();
//
//$hero = [
//    'name' => 'Conan',
//    'hp' => 20,
//    'info' => function() {
//        echo 'Привет ' . $hero['name'];
//    }
//];
//
//$hero['info']();
//
//$hero['name'] = 'Кто-то';
//
//class Unit
//{
//    public int $id;
//    public string $name;
//    public int $hp = 100;
//
//    public function info()
//    {
//        echo 'Привет' . $this->name . PHP_EOL; //PHP_EOL - из-за работы в консоли
//    }
//}
//
//class Hero extends Unit
//{
//    private array $inventory = []; //можно получить доступ только из класса
//    protected array $store = []; //можно получить доступ из класса и из его досерних классов
//}
//
//$hero1 = new Hero();
//$hero1->name = 'Conan';
//$hero1->info();
//
//print_r($hero1);
//
//$hero2 = new Hero();
//$hero2->name = 'Alex';
//$hero2->info();
//
//print_r($hero2);
//
//class Pet extends Unit
//{
//    public function Cat() {
//        echo 'May ' . PHP_EOL;
//    }
//}
//
//$pet1 = new Pet();
//$pet1->name = 'Muroslav';
//$pet1->info();
//$pet1->Cat();
//
//print_r($pet1);
//
//$pet2 = new Pet();
//$pet2->name = 'Puhomir';
//$pet2->info();
//$pet2->Cat();
//
//print_r($pet2);
//
//
//class Unit
//{
//    public ?int $id;
//    public ?string $name;
//    public ?int $hp = 100;
//
//    public function __construct(int $id = null, string $name = null, int $hp = null)
//    {
//        $this->id = $id;
//        $this->name = $name;
//        $this->hp = $hp;
//    }
//
//    public function info()
//    {
//        echo 'Привет' . $this->name . PHP_EOL; //PHP_EOL - из-за работы в консоли
//    }
//}
//
//class Hero extends Unit
//{
//    private array $inventory = []; //можно получить доступ только из класса
//    protected array $store = []; //можно получить доступ из класса и из его досерних классов
//
//    public function __construct(int $id = null, string $name = null, int $hp = null, array $inventory = [], array $store = [])
//    {
//        parent::__construct($id, $name, $hp);
//        $this->inventory = $inventory;
//        $this->store = $store;
//    }
//}
//
//class Pet extends Unit
//{
//    public function Cat() {
//        echo 'May ' . PHP_EOL;
//    }
//}
//
//$hero1 = new Hero(1, 'Conan', 50, ['Меч', 'Щит']);
//$hero2 = new Hero();
//$hero1->info();
//
//print_r($hero1);
//
//$pet1 = new Pet(5, 'Muroslav', 20);
//$pet1->info();
//$pet1->Cat();
//
//print_r($pet1);
//
//
//require_once __DIR__ . '/src/Units/Unit.php';
//require_once __DIR__ . '/src/Units/Hero.php';
//require_once __DIR__ . '/src/Units/Pet.php';
//автозагрузчик
//use \Units\Unit as Unit;
//use \Units\Hero;
//use \Units\Pet;
require_once __DIR__ . '/vendor/autoload.php';

use Akseonov\Php2\Units\{Hero, Pet};
use \Akseonov\Php2\Repositories\InMemoryPetsMemo;
use \Akseonov\Php2\Exceptions\PetNotFountException;

try {
    $faker = Faker\Factory::create();
    $hero1 = new Hero(1, $faker->name(), 50, ['Меч', 'Щит']);

    //Pet::Ping(); // вызов статичного метода из класса до создания экземпляра класса
    $repo = new InMemoryPetsMemo();

    for($i = 0; $i < 10; $i++) {
        $pet = new Pet($i, $faker->name(), 20);
        $repo->save($pet);
    }

    echo $repo->get(15);
} catch (PetNotFountException $exception) {
    echo $exception->getMessage();
} catch (Exception $exception) {
    print_r($exception->getTrace());
}


