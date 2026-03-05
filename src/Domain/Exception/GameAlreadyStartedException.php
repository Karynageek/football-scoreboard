<?php

namespace App\Domain\Exception;

use App\Domain\ValueObject\Team;

class GameAlreadyStartedException extends \DomainException
{
    public function __construct(Team $homeTeam, Team $awayTeam)
    {
        parent::__construct(
            sprintf(
                'Game between %s and %s has already been started',
                $homeTeam->getName(),
                $awayTeam->getName()
            )
        );
    }
}
