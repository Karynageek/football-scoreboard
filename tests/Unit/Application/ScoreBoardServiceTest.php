<?php

namespace App\Tests\Unit\Application;

use App\Application\ScoreBoardService;
use App\Domain\ValueObject\Game;
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
    public function it_adds_to_score_board_when_game_started()
    {
        /* EXECUTE */
        $this->scoreBoardService->startGame('Mexico', 'Canada');

        /* ASSERT */
        $this->assertCount(1, $this->gameRepository->all());

        $game = $this->gameRepository->find('Mexico', 'Canada');
        $this->assertEquals('Mexico', $game->getHomeTeam());
        $this->assertEquals('Canada', $game->getAwayTeam());
        $this->assertEquals(0, $game->getHomeScore());
        $this->assertEquals(0, $game->getAwayScore());
        $this->assertEquals(0, $game->getTotalScore());
    }

    #[Test]
    public function it_throws_an_exception_if_game_already_started()
    {
        /* SETUP */
        $this->scoreBoardService->startGame('Mexico', 'Canada');

        /* ASSERT */
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Game already started');

        /* EXECUTE */
        $this->scoreBoardService->startGame('Mexico', 'Canada');
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
    public function it_throws_an_exception_if_game_not_started_and_finished()
    {
        /* ASSERT */
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Game not started');

        /* EXECUTE */
        $this->scoreBoardService->finishGame('Mexico', 'Canada');
    }

    #[Test]
    public function it_updates_score_when_game_started()
    {
        /* SETUP */
        $this->scoreBoardService->startGame('Mexico', 'Canada');
        $newHomeScore = 1;
        $newAwayScore = 2;

        /* EXECUTE */
        $this->scoreBoardService->updateScore('Mexico', 'Canada', $newHomeScore, $newAwayScore);

        /* ASSERT */
        $this->assertCount(1, $this->gameRepository->all());

        $game = $this->gameRepository->find('Mexico', 'Canada');
        $this->assertEquals('Mexico', $game->getHomeTeam());
        $this->assertEquals('Canada', $game->getAwayTeam());
        $this->assertEquals($newHomeScore, $game->getHomeScore());
        $this->assertEquals($newAwayScore, $game->getAwayScore());
        $this->assertEquals($newHomeScore + $newAwayScore, $game->getTotalScore());
    }

    #[Test]
    public function it_throws_an_exception_if_game_not_started()
    {
        /* ASSERT */
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Game not found');

        /* EXECUTE */
        $this->scoreBoardService->updateScore('Mexico', 'Canada', 1, 2);
    }

    #[Test]
    public function it_gets_summary_of_games_by_total_score()
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
                $game->getHomeTeam(),
                $game->getAwayTeam(),
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

        $this->assertEquals($expected, $actual);
    }
}
