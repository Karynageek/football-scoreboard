<?php

namespace App\Application;

use App\Domain\ValueObject\Game;

class ScoreBoardService
{
    private array $games = [];

    public function startGame(string $homeTeam, $awayTeam): void
    {
        if (!isset($this->games[$homeTeam . '-' . $awayTeam])) {
            $this->games[$homeTeam . '-' . $awayTeam] = new Game(0, 0);
        }
    }

    public function getGames(): array
    {
        return $this->games;
    }
}
