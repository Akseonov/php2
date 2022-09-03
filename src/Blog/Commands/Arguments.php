<?php

namespace Akseonov\Php2\Blog\Commands;

use Akseonov\Php2\Exceptions\ArgumentsException;

class Arguments
{
    private array $arguments = [];

    public function __construct(iterable $arguments)
    {
        foreach ($arguments as $argument => $value) {
            $stringValue = trim((string)$value);
            if (empty($stringValue)) {
                continue;
            }
            $this->arguments[(string)$argument] = $stringValue;
        }
    }

    public static function fromArgv(array $argv): self
    {
        $arguments = [];
        foreach ($argv as $argument) {
            $parts = explode('=', $argument);
            if (count($parts) !== 2) {
                continue;
            }
            $arguments[$parts[0]] = $parts[1];
        }
        return new self($arguments);
    }

    /**
     * @throws ArgumentsException
     */
    public function get(string $argument): string
    {
        if (!array_key_exists($argument, $this->arguments)) {
            throw new ArgumentsException(
                "No such argument: $argument"
            );
        }
        return $this->arguments[$argument];
    }

}