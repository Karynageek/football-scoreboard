<?php

namespace App\Domain;

use App\Domain\ValueObject\Continent;
use App\Domain\ValueObject\Game;
use App\Domain\ValueObject\Team;

interface GameRepositoryInterface
{
    public function save(Game $game): void;

    public function remove(Team $home, Team $away): void;

    public function find(Team $home, Team $away): ?Game;

    public function findByContinent(?Continent $continent = null): array;

    public function isTeamPlaying(Team $team): bool;

    /**
     * @return Game[]
     */
    public function all(): array;
}
