<?php

namespace App\Tests\Unit\Application;

use App\Application\ScoreBoardService;
use App\Domain\ValueObject\Game;
use App\Domain\ValueObject\Team;
use App\Infrastructure\Repository\GameRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ScoreBoardServiceTest extends TestCase
{
    private GameRepository $gameRepository;
    private ScoreBoardService $scoreBoardService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gameRepository = new GameRepository();
        $this->scoreBoardService = new ScoreBoardService($this->gameRepository);
    }

    #[Test]
    public function it_starts_game_with_zero_score()
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
    public function it_throws_an_exception_if_game_already_started()
    {
        /* SETUP */
        $this->scoreBoardService->startGame('Mexico', 'Canada');

        /* ASSERT */
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Game already started');

        /* EXECUTE */
        $this->scoreBoardService->startGame('Mexico', 'Canada');
    }

    #[Test]
    public function it_throws_exception_when_home_and_away_are_same(): void
    {
        /* ASSERT */
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Home and Away teams must be different');

        /* EXECUTE */
        $this->scoreBoardService->startGame('Mexico', 'Mexico');
    }

    #[Test]
    public function it_treats_team_names_case_insensitively(): void
    {
        /* SETUP */
        $this->scoreBoardService->startGame('Mexico', 'Canada');

        /* ASSERT */
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Game already started');

        /* EXECUTE */
        $this->scoreBoardService->startGame('mexico', 'canada');
    }

    #[Test]
    public function it_throws_exception_when_team_name_is_empty()
    {
        /* ASSERT */
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Team name cannot be empty');

        /* EXECUTE */
        $this->scoreBoardService->startGame('Mexico', '');
    }

    #[Test]
    public function it_removes_from_score_board_when_game_finished()
    {
        /* SETUP */
        $this->scoreBoardService->startGame('Mexico', 'Canada');

        /* EXECUTE */
        $this->scoreBoardService->finishGame('Mexico', 'Canada');

        /* ASSERT */
        $this->assertCount(0, $this->gameRepository->all());
    }

    #[Test]
    public function it_throws_an_exception_if_finished_non_existing_game()
    {
        /* ASSERT */
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Game not found');

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
    public function it_updates_existing_game_score()
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
    public function it_throws_an_exception_if_game_not_started()
    {
        /* ASSERT */
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Game not found');

        /* EXECUTE */
        $this->scoreBoardService->updateScore('Mexico', 'Canada', 1, 2);
    }

    #[Test]
    public function it_throws_exception_for_negative_scores(): void
    {
        /* SETUP */
        $this->scoreBoardService->startGame('Mexico', 'Canada');

        /* ASSERT */
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Scores cannot be negative');

        /* EXECUTE */
        $this->scoreBoardService->updateScore('Mexico', 'Canada', -1, 2);
    }

    #[Test]
    public function it_returns_games_sorted_by_total_score_and_recency()
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
}
