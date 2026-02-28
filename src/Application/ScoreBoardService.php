<?php

namespace App\Application;

use App\Domain\ValueObject\Game;

class ScoreBoardService
{
    private array $games = [];

    public function startGame(string $homeTeam, $awayTeam): void
    {
        if (isset($this->games[$homeTeam . '-' . $awayTeam])) {
            throw new \Exception('Game already started');
        }

        $this->games[$homeTeam . '-' . $awayTeam] = new Game(0, 0);
    }

    public function finishGame(string $homeTeam, $awayTeam): void
    {
        if (!isset($this->games[$homeTeam . '-' . $awayTeam])) {
            throw new \Exception('Game not started');
        }

        unset($this->games[$homeTeam . '-' . $awayTeam]);
    }

    public function getGames(): array
    {
        return $this->games;
    }
}
