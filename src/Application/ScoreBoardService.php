<?php

namespace App\Application;

use App\Domain\ValueObject\Game;
use App\Domain\ValueObject\Team;
use App\Infrastructure\Repository\GameRepository;

class ScoreBoardService
{
    public function __construct(
        private GameRepository $gameRepository
    ) {
    }

    public function startGame(string $homeTeam, string $awayTeam): void
    {
        $home = new Team($homeTeam);
        $away = new Team($awayTeam);

        if ($this->gameRepository->find($home, $away)) {
            throw new \InvalidArgumentException('Game already started');
        }

        $this->gameRepository->save(new Game($home, $away));
    }

    public function finishGame(string $homeTeam, string $awayTeam): void
    {
        $home = new Team($homeTeam);
        $away = new Team($awayTeam);

        $game = $this->gameRepository->find($home, $away);

        if (!$game) {
            throw new \InvalidArgumentException('Game not found');
        }

        $this->gameRepository->remove($home, $away);
    }

    public function updateScore(string $homeTeam, string $awayTeam, int $homeScore, int $awayScore): void
    {
        $game = $this->gameRepository->find(new Team($homeTeam), new Team($awayTeam));

        if (!$game) {
            throw new \InvalidArgumentException('Game not found');
        }

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
