<?php

namespace Akseonov\Php2\Repositories;

use Akseonov\Php2\Units\Pet;
use Akseonov\Php2\Exceptions\PetNotFountException;

class InMemoryPetsMemo
{
    private array $pets = [];

    public function save(Pet $pet): void {
        $this->pets[] = $pet;
    }

    public function get(int $id):Pet {
        foreach ($this->pets as $pet) {
            if ($pet->id() === $id) {
                return $pet;
            }
        }
        throw new PetNotFountException('Нет такого питомца', 404);
    }
}