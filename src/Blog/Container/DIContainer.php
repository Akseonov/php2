<?php

namespace Akseonov\Php2\Blog\Container;

use Akseonov\Php2\Exceptions\NotFoundException;
use Psr\Container\ContainerInterface;
use ReflectionClass;

class DIContainer implements ContainerInterface
{
    private array $resolves = [];


    public function bind(string $type, $resolver): void
    {
        $this->resolves[$type] = $resolver;
    }

    /**
     * @throws NotFoundException
     */
    public function get(string $type): object
    {
        if (array_key_exists($type, $this->resolves)) {
            $typeToCreate = $this->resolves[$type];

            if (is_object($typeToCreate)) {
                return $typeToCreate;
            }

            return $this->get($typeToCreate);
        }

        if (!class_exists($type)) {
            throw new NotFoundException("Cannot resolve type: $type");
        }

        $reflectionClass = new ReflectionClass($type);
        $constructor = $reflectionClass->getConstructor();

        if ($constructor === null) {
            return new $type;
        }

        $parameters = [];

        foreach ($constructor->getParameters() as $parameter) {
            $parameterType = $parameter->getType()->getName();
            $parameters[] = $this->get($parameterType);
        }

        return new $type(...$parameters);
    }

    public function has(string $id): bool
    {
        try {
            $this->get($id);
        } catch (NotFoundException $exception) {
            return false;
        }
        return true;
    }
}