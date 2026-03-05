<?php

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidScoreException;
use App\Domain\Exception\SameTeamException;

class Game
{
    private int $homeScore = 0;
    private int $awayScore = 0;
    private ?int $id = null;

    public function __construct(
        private Team $homeTeam,
        private Team $awayTeam
    ) {
        if ($this->homeTeam->equals($this->awayTeam)) {
            throw new SameTeamException($this->homeTeam);
        }
    }

    public function getHomeTeam(): Team
    {
        return $this->homeTeam;
    }

    public function getAwayTeam(): Team
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

    public function setId(int $id): void
    {
        if ($this->id !== null) {
            throw new \InvalidArgumentException('ID has already been assigned to this game.');
        }
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function updateScore(int $homeScore, int $awayScore): void
    {
        if ($homeScore < 0 || $awayScore < 0) {
            throw new InvalidScoreException($homeScore, $awayScore);
        }
        $this->homeScore = $homeScore;
        $this->awayScore = $awayScore;
    }
}
