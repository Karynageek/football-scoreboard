<?php

namespace App\Application;

use App\Domain\Exception\GameNotFoundException;
use App\Domain\Exception\TeamAlreadyPlayingException;
use App\Domain\GameRepositoryInterface;
use App\Domain\ValueObject\Continent;
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

        $this->validateTeamAvailability($home, $away);

        $this->gameRepository->save(new Game($home, $away));
    }

    private function validateTeamAvailability(Team $home, Team $away): void
    {
        if ($this->gameRepository->isTeamPlaying($home)) {
            throw new TeamAlreadyPlayingException($home);
        }

        if ($this->gameRepository->isTeamPlaying($away)) {
            throw new TeamAlreadyPlayingException($away);
        }
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

    public function getSummaryOfGamesByTotalScore(?string $continent = null): array
    {
        $continentEnum = $continent ? Continent::tryFrom($continent) : null;
        $games = $this->gameRepository->findByContinent($continentEnum);

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
