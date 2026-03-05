<?php

namespace App\Domain\Exception;

use App\Domain\ValueObject\Team;

class TeamAlreadyPlayingException extends \DomainException
{
    public function __construct(Team $team)
    {
        parent::__construct(
            sprintf('Team %s is already playing in another game', $team->getName())
        );
    }
}
