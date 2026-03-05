<?php

namespace App\Domain\Exception;

use App\Domain\ValueObject\Team;

class GameNotFoundException extends \DomainException
{
    public function __construct(Team $homeTeam, Team $awayTeam)
    {
        parent::__construct(
            sprintf(
                'Game between %s and %s not found',
                $homeTeam->getName(),
                $awayTeam->getName()
            )
        );
    }
}
