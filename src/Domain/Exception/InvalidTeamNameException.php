<?php

namespace App\Domain\Exception;

class InvalidTeamNameException extends \DomainException
{
    public function __construct(string $teamName)
    {
        parent::__construct(
            sprintf('Team name "%s" is invalid: name cannot be empty', $teamName)
        );
    }
}
