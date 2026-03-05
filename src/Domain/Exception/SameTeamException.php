<?php

namespace App\Domain\Exception;

use App\Domain\ValueObject\Team;

class SameTeamException extends \DomainException
{
    public function __construct(Team $team)
    {
        parent::__construct(
            sprintf(
                'Home and Away teams must be different (both are %s)',
                $team->getName()
            )
        );
    }
}
