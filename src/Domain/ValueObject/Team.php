<?php

namespace App\Domain\ValueObject;

class Team
{
    private string $name;

    public function __construct(string $name) {
        $trimmed = ucfirst(strtolower(trim($name)));

        if (empty($trimmed)) {
            throw new \InvalidArgumentException('Team name cannot be empty');
        }

        $this->name = $trimmed;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function equals(Team $other): bool
    {
        return $this->name === $other->name;
    }
}
