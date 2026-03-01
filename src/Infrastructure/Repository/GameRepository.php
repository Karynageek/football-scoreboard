<?php

namespace App\Infrastructure\Repository;

use App\Domain\GameRepositoryInterface;
use App\Domain\ValueObject\Game;

class GameRepository implements GameRepositoryInterface
{
    private array $games = [];

    public function add(string $homeTeam, string $awayTeam, int $id): void
    {
        if (isset($this->games[$homeTeam . '-' . $awayTeam])) {
            throw new \Exception('Game already started');
        }

        $this->games[$homeTeam . '-' . $awayTeam] = new Game($homeTeam, $awayTeam, $id);
    }

    public function remove(string $homeTeam, string $awayTeam): void
    {
        if (!isset($this->games[$homeTeam . '-' . $awayTeam])) {
            throw new \Exception('Game not started');
        }
        unset($this->games[$homeTeam . '-' . $awayTeam]);
    }

    public function find(string $homeTeam, string $awayTeam): Game
    {
        if (!isset($this->games[$homeTeam . '-' . $awayTeam])) {
            throw new \Exception('Game not found');
        }

        return $this->games[$homeTeam . '-' . $awayTeam];
    }

    public function all(): array
    {
        return array_values($this->games);
    }
}
