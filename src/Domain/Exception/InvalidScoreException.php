<?php

namespace App\Domain\Exception;

class InvalidScoreException extends \DomainException
{
    public function __construct(int $homeScore, int $awayScore)
    {
        parent::__construct(
            sprintf(
                'Invalid score: %d-%d. Scores cannot be negative',
                $homeScore,
                $awayScore
            )
        );
    }
}
