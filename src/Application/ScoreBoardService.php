<?php

namespace App\Application;

use App\Domain\Exception\GameAlreadyStartedException;
use App\Domain\Exception\GameNotFoundException;
use App\Domain\GameRepositoryInterface;
use App\Domain\ValueObject\Game;
use App\Domain\ValueObject\Team;

class ScoreBoardService
{
    public function __construct(
        private GameRepositoryInterface $gameRepository
    ) {
    }

    public function startGame(string $homeTeam, string $awayTeam): void
    {
        $home = new Team($homeTeam);
        $away = new Team($awayTeam);

        if ($this->gameRepository->find($home, $away)) {
            throw new GameAlreadyStartedException($home, $away);
        }

        $this->gameRepository->save(new Game($home, $away));
    }

    public function finishGame(string $homeTeam, string $awayTeam): void
    {
        $home = new Team($homeTeam);
        $away = new Team($awayTeam);

        $game = $this->gameRepository->find($home, $away);

        if (!$game) {
            throw new GameNotFoundException($home, $away);
        }

        $this->gameRepository->remove($home, $away);
    }

    public function updateScore(string $homeTeam, string $awayTeam, int $homeScore, int $awayScore): void
    {
        $home = new Team($homeTeam);
        $away = new Team($awayTeam);

        $game = $this->gameRepository->find($home, $away);

        if (!$game) {
            throw new GameNotFoundException($home, $away);
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

            // Games with the same total score will be returned
            // ordered by the most recently added
            return $b->getId() <=> $a->getId();
        });

        return $games;
    }
}
