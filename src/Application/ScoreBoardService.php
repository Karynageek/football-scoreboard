<?php

namespace App\Application;

use App\Domain\ValueObject\Game;
use App\Infrastructure\Repository\GameRepository;

class ScoreBoardService
{
    private int $orderCounter = 0;

    public function __construct(
        private GameRepository $gameRepository
    ) {
    }

    public function startGame(string $homeTeam, string $awayTeam): void
    {
        $this->orderCounter++;
        $this->gameRepository->add($homeTeam, $awayTeam, $this->orderCounter);
    }

    public function finishGame(string $homeTeam, string $awayTeam): void
    {
        $this->gameRepository->remove($homeTeam, $awayTeam);
    }

    public function updateScore(string $homeTeam, string $awayTeam, int $homeScore, int $awayScore): void
    {
        $game = $this->gameRepository->find($homeTeam, $awayTeam);
        $game->updateScore($homeScore, $awayScore);
    }

    public function getSummaryOfGamesByTotalScore(): array
    {
        $games = $this->gameRepository->all();

        usort($games, function (Game $a, Game $b) {
            $scoreComparison = $b->getTotalScore() <=> $a->getTotalScore();

            if ($scoreComparison !== 0) {
                return $scoreComparison;
            }

            // Most recently added first
            return $b->getId() <=> $a->getId();
        });

        return $games;
    }
}
