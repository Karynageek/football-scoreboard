<?php

namespace App\Tests\Unit\Application;

use App\Application\ScoreBoardService;
use App\Domain\Exception\GameNotFoundException;
use App\Domain\Exception\InvalidScoreException;
use App\Domain\Exception\InvalidTeamNameException;
use App\Domain\Exception\SameTeamException;
use App\Domain\Exception\TeamAlreadyPlayingException;
use App\Domain\GameRepositoryInterface;
use App\Domain\ValueObject\Game;
use App\Domain\ValueObject\Team;
use App\Tests\KernelTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ScoreBoardServiceTest extends KernelTestCase
{
    private GameRepositoryInterface $gameRepository;
    private ScoreBoardService $scoreBoardService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scoreBoardService = $this->getService(ScoreBoardService::class);
        $this->gameRepository = $this->getService(GameRepositoryInterface::class);
    }

    #[Test]
    public function it_starts_game_with_zero_score(): void
    {
        /* EXECUTE */
        $this->scoreBoardService->startGame('Mexico', 'Canada');

        /* ASSERT */
        $this->assertCount(1, $this->gameRepository->all());

        $game = $this->gameRepository->find(new Team('Mexico'), new Team('Canada'));

        $this->assertSame('Mexico', $game->getHomeTeam()->getName());
        $this->assertSame('Canada', $game->getAwayTeam()->getName());
        $this->assertSame(0, $game->getHomeScore());
        $this->assertSame(0, $game->getAwayScore());
        $this->assertSame(0, $game->getTotalScore());
    }

    #[Test]
    #[DataProvider('alreadyPlayingTeamProvider')]
    public function it_throws_exception_when_team_is_already_playing(
        array $existingGames,
        string $newHomeTeam,
        string $newAwayTeam,
        string $alreadyPlayingTeam
    ): void {
        /* SETUP */
        foreach ($existingGames as [$home, $away]) {
            $this->scoreBoardService->startGame($home, $away);
        }

        /* ASSERT */
        $this->expectException(TeamAlreadyPlayingException::class);
        $this->expectExceptionMessage(
            sprintf('Team %s is already playing in another game', $alreadyPlayingTeam)
        );

        /* EXECUTE */
        $this->scoreBoardService->startGame($newHomeTeam, $newAwayTeam);
    }

    public static function alreadyPlayingTeamProvider(): array
    {
        return [
            'same teams same order' => [
                'existingGames' => [
                    ['Mexico', 'Canada'],
                ],
                'newHomeTeam' => 'Mexico',
                'newAwayTeam' => 'Canada',
                'alreadyPlayingTeam' => 'Mexico',
            ],
            'same teams reversed order' => [
                'existingGames' => [
                    ['Mexico', 'Canada'],
                ],
                'newHomeTeam' => 'Canada',
                'newAwayTeam' => 'Mexico',
                'alreadyPlayingTeam' => 'Canada',
            ],
            'home team already playing' => [
                'existingGames' => [
                    ['Spain', 'Brazil'],
                    ['Germany', 'France'],
                ],
                'newHomeTeam' => 'Spain',
                'newAwayTeam' => 'France',
                'alreadyPlayingTeam' => 'Spain',
            ],
            'away team already playing' => [
                'existingGames' => [
                    ['Austria', 'Brazil'],
                    ['Germany', 'France'],
                ],
                'newHomeTeam' => 'Spain',
                'newAwayTeam' => 'France',
                'alreadyPlayingTeam' => 'France',
            ],
        ];
    }

    #[Test]
    public function it_throws_exception_when_home_and_away_are_same(): void
    {
        /* ASSERT */
        $this->expectException(SameTeamException::class);
        $this->expectExceptionMessage('Home and Away teams must be different (both are Mexico)');

        /* EXECUTE */
        $this->scoreBoardService->startGame('Mexico', 'Mexico');
    }

    #[Test]
    #[DataProvider('caseInsensitiveTeamNamesProvider')]
    public function it_treats_team_names_case_insensitively(
        string $canonicalHomeTeam,
        string $canonicalAwayTeam,
        string $variantHomeTeam,
        string $variantAwayTeam
    ): void
    {
        /* SETUP */
        $this->scoreBoardService->startGame($canonicalHomeTeam, $canonicalAwayTeam);

        /* ASSERT */
        $this->expectException(TeamAlreadyPlayingException::class);
        $this->expectExceptionMessage(
            sprintf('Team %s is already playing in another game', $canonicalHomeTeam)
        );

        /* EXECUTE */
        $this->scoreBoardService->startGame($variantHomeTeam, $variantAwayTeam);
    }

    public static function caseInsensitiveTeamNamesProvider(): array
    {
        return [
            'all lowercase' => ['Mexico', 'Canada', 'mexico', 'canada'],
            'all uppercase' => ['Mexico', 'Canada', 'MEXICO', 'CANADA'],
            'mixed case' => ['Mexico', 'Canada', 'MeXiCo', 'CaNaDa'],
            'with extra spaces' => ['Mexico', 'Canada', '  mexico  ', '  canada  '],
            'uppercase with spaces' => ['Mexico', 'Canada', '  MEXICO  ', '  CANADA  '],
        ];
    }

    #[Test]
    #[DataProvider('caseInsensitiveTeamNamesProvider')]
    public function it_finishes_game_with_case_insensitive_names(
        string $canonicalHomeTeam,
        string $canonicalAwayTeam,
        string $variantHomeTeam,
        string $variantAwayTeam
    ): void
    {
        /* SETUP */
        $this->scoreBoardService->startGame($canonicalHomeTeam, $canonicalAwayTeam);

        /* EXECUTE */
        $this->scoreBoardService->finishGame($variantHomeTeam, $variantAwayTeam);

        /* ASSERT */
        $this->assertCount(0, $this->gameRepository->all());
    }

    #[Test]
    #[DataProvider('caseInsensitiveTeamNamesProvider')]
    public function it_updates_score_with_case_insensitive_names(
        string $canonicalHomeTeam,
        string $canonicalAwayTeam,
        string $variantHomeTeam,
        string $variantAwayTeam
    ): void
    {
        /* SETUP */
        $this->scoreBoardService->startGame($canonicalHomeTeam, $canonicalAwayTeam);

        /* EXECUTE */
        $this->scoreBoardService->updateScore($variantHomeTeam, $variantAwayTeam, 2, 1);

        /* ASSERT */
        $game = $this->gameRepository->find(new Team('Mexico'), new Team('Canada'));
        $this->assertSame(2, $game->getHomeScore());
        $this->assertSame(1, $game->getAwayScore());
    }

    #[Test]
    public function it_replaces_score_when_updated_multiple_times(): void
    {
        /* SETUP */
        $this->scoreBoardService->startGame('Mexico', 'Canada');

        /* EXECUTE */
        $this->scoreBoardService->updateScore('Mexico', 'Canada', 1, 0);
        $this->scoreBoardService->updateScore('Mexico', 'Canada', 2, 1);
        $this->scoreBoardService->updateScore('Mexico', 'Canada', 3, 2);

        /* ASSERT */
        $game = $this->gameRepository->find(new Team('Mexico'), new Team('Canada'));
        $this->assertSame(3, $game->getHomeScore());
        $this->assertSame(2, $game->getAwayScore());
    }

    #[Test]
    public function it_throws_exception_when_team_name_is_empty(): void
    {
        /* ASSERT */
        $this->expectException(InvalidTeamNameException::class);
        $this->expectExceptionMessage('Team name "" is invalid: name cannot be empty');

        /* EXECUTE */
        $this->scoreBoardService->startGame('Mexico', '');
    }

    #[Test]
    public function it_removes_from_score_board_when_game_finished(): void
    {
        /* SETUP */
        $this->scoreBoardService->startGame('Mexico', 'Canada');

        /* EXECUTE */
        $this->scoreBoardService->finishGame('Mexico', 'Canada');

        /* ASSERT */
        $this->assertCount(0, $this->gameRepository->all());
    }

    #[Test]
    public function it_finishes_game_when_teams_given_in_reversed_order(): void
    {
        /* SETUP */
        $this->scoreBoardService->startGame('Mexico', 'Canada');

        /* EXECUTE */
        $this->scoreBoardService->finishGame('Canada', 'Mexico');

        /* ASSERT */
        $this->assertCount(0, $this->gameRepository->all());
    }

    #[Test]
    public function it_throws_an_exception_if_finished_non_existing_game(): void
    {
        /* ASSERT */
        $this->expectException(GameNotFoundException::class);
        $this->expectExceptionMessage('Game between Mexico and Canada not found');

        /* EXECUTE */
        $this->scoreBoardService->finishGame('Mexico', 'Canada');
    }

    #[Test]
    public function it_finishes_only_specific_game(): void
    {
        /* SETUP */
        $this->scoreBoardService->startGame('Mexico', 'Canada');
        $this->scoreBoardService->startGame('Spain', 'Brazil');

        /* EXECUTE */
        $this->scoreBoardService->finishGame('Mexico', 'Canada');

        /* ASSERT */
        $this->assertCount(1, $this->gameRepository->all());
        $this->assertNotNull($this->gameRepository->find(new Team('Spain'), new Team('Brazil')));
    }

    #[Test]
    public function it_updates_score_when_teams_given_in_reversed_order(): void
    {
        /* SETUP */
        $this->scoreBoardService->startGame('Mexico', 'Canada');

        /* EXECUTE */
        $this->scoreBoardService->updateScore('Canada', 'Mexico', 3, 1);

        /* ASSERT */
        $game = $this->gameRepository->find(new Team('Mexico'), new Team('Canada'));
        $this->assertSame(3, $game->getHomeScore());
        $this->assertSame(1, $game->getAwayScore());
    }

    #[Test]
    public function it_updates_existing_game_score(): void
    {
        /* SETUP */
        $this->scoreBoardService->startGame('Mexico', 'Canada');
        $newHomeScore = 1;
        $newAwayScore = 2;

        /* EXECUTE */
        $this->scoreBoardService->updateScore('Mexico', 'Canada', $newHomeScore, $newAwayScore);

        /* ASSERT */
        $this->assertCount(1, $this->gameRepository->all());

        $game = $this->gameRepository->find(new Team('Mexico'), new Team('Canada'));
        $this->assertSame('Mexico', $game->getHomeTeam()->getName());
        $this->assertSame('Canada', $game->getAwayTeam()->getName());
        $this->assertSame($newHomeScore, $game->getHomeScore());
        $this->assertSame($newAwayScore, $game->getAwayScore());
        $this->assertSame($newHomeScore + $newAwayScore, $game->getTotalScore());
    }

    #[Test]
    public function it_throws_an_exception_if_game_not_started(): void
    {
        /* ASSERT */
        $this->expectException(GameNotFoundException::class);
        $this->expectExceptionMessage('Game between Mexico and Canada not found');

        /* EXECUTE */
        $this->scoreBoardService->updateScore('Mexico', 'Canada', 1, 2);
    }

    #[Test]
    #[DataProvider('negativeScoreProvider')]
    public function it_throws_exception_for_negative_scores(
        int $homeScore,
        int $awayScore,
        string $expectedMessage
    ): void
    {
        /* SETUP */
        $this->scoreBoardService->startGame('Mexico', 'Canada');

        /* ASSERT */
        $this->expectException(InvalidScoreException::class);
        $this->expectExceptionMessage($expectedMessage);

        /* EXECUTE */
        $this->scoreBoardService->updateScore('Mexico', 'Canada', $homeScore, $awayScore);
    }

    public static function negativeScoreProvider(): array
    {
        return [
            'home score negative' => [-1, 2, 'Invalid score: -1-2. Scores cannot be negative'],
            'away score negative' => [1, -1, 'Invalid score: 1--1. Scores cannot be negative'],
            'both scores negative' => [-1, -2, 'Invalid score: -1--2. Scores cannot be negative'],
        ];
    }

    #[Test]
    public function it_returns_games_sorted_by_total_score_and_recency(): void
    {
        /* SETUP */
        $this->scoreBoardService->startGame('Mexico', 'Canada');
        $this->scoreBoardService->startGame('Spain', 'Brazil');
        $this->scoreBoardService->startGame('Germany', 'France');
        $this->scoreBoardService->startGame('Uruguay', 'Italy');
        $this->scoreBoardService->startGame('Argentina', 'Australia');

        $this->scoreBoardService->updateScore('Mexico', 'Canada', 0, 5);
        $this->scoreBoardService->updateScore('Spain', 'Brazil', 10, 2);
        $this->scoreBoardService->updateScore('Germany', 'France', 2, 2);
        $this->scoreBoardService->updateScore('Uruguay', 'Italy', 6, 6);
        $this->scoreBoardService->updateScore('Argentina', 'Australia', 3, 1);

        /* EXECUTE */
        $result = $this->scoreBoardService->getSummaryOfGamesByTotalScore();

        /* ASSERT */
        $this->assertCount(5, $result);

        $actual = array_map(
            fn (Game $game) => sprintf(
                '%s-%s:%d-%d',
                $game->getHomeTeam()->getName(),
                $game->getAwayTeam()->getName(),
                $game->getHomeScore(),
                $game->getAwayScore()
            ),
            $result
        );

        $expected = [
            'Uruguay-Italy:6-6',
            'Spain-Brazil:10-2',
            'Mexico-Canada:0-5',
            'Argentina-Australia:3-1',
            'Germany-France:2-2',
        ];

        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function it_returns_empty_summary_when_no_games_exist(): void
    {
        /* EXECUTE */
        $result = $this->scoreBoardService->getSummaryOfGamesByTotalScore();

        /* ASSERT */
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    #[Test]
    public function it_returns_single_game_in_summary(): void
    {
        /* SETUP */
        $this->scoreBoardService->startGame('Mexico', 'Canada');
        $this->scoreBoardService->updateScore('Mexico', 'Canada', 2, 1);

        /* EXECUTE */
        $result = $this->scoreBoardService->getSummaryOfGamesByTotalScore();

        /* ASSERT */
        $this->assertCount(1, $result);
        $game = $result[0];
        $this->assertSame('Mexico', $game->getHomeTeam()->getName());
        $this->assertSame('Canada', $game->getAwayTeam()->getName());
        $this->assertSame(2, $game->getHomeScore());
        $this->assertSame(1, $game->getAwayScore());
    }

    #[Test]
    public function it_excludes_finished_games_from_summary(): void
    {
        /* SETUP */
        $this->scoreBoardService->startGame('Mexico', 'Canada');
        $this->scoreBoardService->startGame('Spain', 'Brazil');
        $this->scoreBoardService->startGame('Germany', 'France');

        $this->scoreBoardService->updateScore('Mexico', 'Canada', 0, 5);
        $this->scoreBoardService->updateScore('Spain', 'Brazil', 10, 2);
        $this->scoreBoardService->updateScore('Germany', 'France', 2, 2);

        /* EXECUTE */
        $this->scoreBoardService->finishGame('Spain', 'Brazil');
        $result = $this->scoreBoardService->getSummaryOfGamesByTotalScore();

        /* ASSERT */
        $this->assertCount(2, $result);

        $actual = array_map(
            fn (Game $game) => sprintf(
                '%s-%s:%d-%d',
                $game->getHomeTeam()->getName(),
                $game->getAwayTeam()->getName(),
                $game->getHomeScore(),
                $game->getAwayScore()
            ),
            $result
        );

        $expected = [
            'Mexico-Canada:0-5',
            'Germany-France:2-2',
        ];

        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function it_includes_only_teams_by_continent_in_summary(): void
    {
        /* SETUP */
        $this->scoreBoardService->startGame('Mexico', 'Canada');
        $this->scoreBoardService->startGame('Spain', 'Brazil');
        $this->scoreBoardService->startGame('Germany', 'France');

        $this->scoreBoardService->updateScore('Mexico', 'Canada', 0, 5);
        $this->scoreBoardService->updateScore('Spain', 'Brazil', 10, 2);
        $this->scoreBoardService->updateScore('Germany', 'France', 2, 2);

        /* EXECUTE */
        $result = $this->scoreBoardService->getSummaryOfGamesByTotalScore('Europe');

        /* ASSERT */
        $this->assertCount(2, $result);

        $actual = array_map(
            fn (Game $game) => sprintf(
                '%s-%s:%d-%d',
                $game->getHomeTeam()->getName(),
                $game->getAwayTeam()->getName(),
                $game->getHomeScore(),
                $game->getAwayScore()
            ),
            $result
        );

        $expected = [
            'Spain-Brazil:10-2',
            'Germany-France:2-2',
        ];

        $this->assertSame($expected, $actual);
    }

}
