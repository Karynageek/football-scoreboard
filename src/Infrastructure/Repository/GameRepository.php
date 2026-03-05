<?php

namespace App\Infrastructure\Repository;

use App\Domain\GameRepositoryInterface;
use App\Domain\ValueObject\Continent;
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
        $key = $this->findKey($home, $away);

        if ($key !== null) {
            unset($this->games[$key]);
        }
    }

    public function find(Team $home, Team $away): ?Game
    {
        $key = $this->findKey($home, $away);

        if ($key !== null) {
            return $this->games[$key];
        }

        return null;
    }

    public function isTeamPlaying(Team $team): bool
    {
        foreach ($this->games as $game) {
            if ($game->getHomeTeam()->equals($team) || $game->getAwayTeam()->equals($team)) {
                return true;
            }
        }

        return false;
    }

    public function findByContinent(?Continent $continent = null): array
    {
        $games = $this->games;

        if ($continent !== null) {
            $games = array_filter(
                $games,
                fn(Game $game) =>
                    $continent->containsTeam($game->getHomeTeam()) ||
                    $continent->containsTeam($game->getAwayTeam())
            );
        }

        return array_values($games);
    }

    public function all(): array
    {
        return array_values($this->games);
    }

    private function generateKey(Team $home, Team $away): string
    {
        return "{$home->getName()}-{$away->getName()}";
    }

    private function findKey(Team $home, Team $away): ?string
    {
        $directKey = $this->generateKey($home, $away);
        if (isset($this->games[$directKey])) {
            return $directKey;
        }

        $reverseKey = $this->generateKey($away, $home);
        if (isset($this->games[$reverseKey])) {
            return $reverseKey;
        }

        return null;
    }
}
