<?php

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidTeamNameException;

class Team
{
    private string $name;

    public function __construct(string $name) {
        $trimmed = ucfirst(strtolower(trim($name)));

        if (empty($trimmed)) {
            throw new InvalidTeamNameException($name);
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
