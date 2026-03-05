<?php

namespace App\Domain;

use App\Domain\ValueObject\Game;
use App\Domain\ValueObject\Team;

interface GameRepositoryInterface
{
    public function save(Game $game): void;

    public function remove(Team $home, Team $away): void;

    public function find(Team $home, Team $away): ?Game;

    /**
     * @return Game[]
     */
    public function all(): array;
}
