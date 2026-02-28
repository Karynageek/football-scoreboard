<?php

namespace App\Domain\ValueObject;

class Game
{
    public function __construct(private int $homeScore = 0, private int $awayScore = 0)
    {
    }
}
