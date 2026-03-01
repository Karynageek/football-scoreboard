<?php

namespace App\Domain\ValueObject;

class Game
{
    private int $homeScore = 0;
    private int $awayScore = 0;

    public function __construct(
        private string $homeTeam,
        private string $awayTeam,
        private string $id
    )
    {
    }

    public function getHomeTeam(): string
    {
        return $this->homeTeam;
    }

    public function getAwayTeam(): string
    {
        return $this->awayTeam;
    }

    public function getHomeScore(): int
    {
        return $this->homeScore;
    }

    public function getAwayScore(): int
    {
        return $this->awayScore;
    }

    public function getTotalScore(): int
    {
        return $this->homeScore + $this->awayScore;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function updateScore(int $homeScore, int $awayScore): void
    {
        $this->homeScore = $homeScore;
        $this->awayScore = $awayScore;
    }
}
