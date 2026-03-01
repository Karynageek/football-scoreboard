<?php

namespace App\Tests\Unit\Application;

use App\Application\ScoreBoardService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ScoreBoardServiceTest extends TestCase
{
    private ScoreBoardService $scoreBoardService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scoreBoardService = new ScoreBoardService();
    }

    #[Test]
    public function it_adds_to_score_board_when_game_started()
    {
        /* EXECUTE */
        $this->scoreBoardService->startGame('Mexico', 'Canada');

        /* ASSERT */
        $this->assertCount(1, $this->scoreBoardService->getGames());

        $game = $this->scoreBoardService->getGames()['Mexico-Canada'];
        $this->assertEquals(0, $game->getHomeScore());
        $this->assertEquals(0, $game->getAwayScore());
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
        $this->assertCount(0, $this->scoreBoardService->getGames());
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
        $this->assertCount(1, $this->scoreBoardService->getGames());

        $game = $this->scoreBoardService->getGames()['Mexico-Canada'];
        $this->assertEquals($newHomeScore, $game->getHomeScore());
        $this->assertEquals($newAwayScore, $game->getAwayScore());
    }

    #[Test]
    public function it_throws_an_exception_if_game_not_started()
    {
        /* ASSERT */
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Game not started');

        /* EXECUTE */
        $this->scoreBoardService->updateScore('Mexico', 'Canada', 1, 2);
    }
}
