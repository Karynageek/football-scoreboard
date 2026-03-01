<?php

namespace App\Domain;

use App\Domain\ValueObject\Game;

interface GameRepositoryInterface
{
    public function add(string $homeTeam, string $awayTeam, int $id): void;

    public function remove(string $homeTeam, string $awayTeam): void;

    public function find(string $homeTeam, string $awayTeam): Game;

    /**
     * @return Game[]
     */
    public function all(): array;
}
