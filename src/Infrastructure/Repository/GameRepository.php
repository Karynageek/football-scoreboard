<?php

namespace App\Infrastructure\Repository;

use App\Domain\GameRepositoryInterface;
use App\Domain\ValueObject\Game;
use App\Domain\ValueObject\Team;

class GameRepository implements GameRepositoryInterface
{
    /** @var array<string, Game> */
    private array $games = [];
    private int $idCounter = 0; // Used for recency sorting

    public function save(Game $game): void
    {
        if ($game->getId() === null) {
            $game->setId(++$this->idCounter);
        }

        $this->games[$this->generateKey($game->getHomeTeam(), $game->getAwayTeam())] = $game;
    }

    public function remove(Team $home, Team $away): void
    {
        unset($this->games[$this->generateKey($home, $away)]);
    }

    public function find(Team $home, Team $away): ?Game
    {
        return $this->games[$this->generateKey($home, $away)] ?? null;
    }

    public function all(): array
    {
        return array_values($this->games);
    }

    private function generateKey(Team $home, Team $away): string
    {
        return "{$home->getName()}-{$away->getName()}";
    }
}
