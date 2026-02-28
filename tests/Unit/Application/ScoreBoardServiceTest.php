<?php

namespace App\Tests\Unit\Application;

use App\Application\ScoreBoardService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ScoreBoardServiceTest extends TestCase
{
    #[Test]
    public function it_adds_to_score_board_when_game_started()
    {
        /* EXECUTE */
        $scoreBoardService = new ScoreBoardService();
        $scoreBoardService->startGame('Mexico', 'Canada');

        /* ASSERT */
        $this->assertCount(1, $scoreBoardService->getGames());
    }

    #[Test]
    public function it_throws_an_exception_if_game_already_started()
    {
        /* SETUP */
        $scoreBoardService = new ScoreBoardService();
        $scoreBoardService->startGame('Mexico', 'Canada');

        /* ASSERT */
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Game already started');

        /* EXECUTE */
        $scoreBoardService->startGame('Mexico', 'Canada');
    }

    #[Test]
    public function it_removes_from_score_board_when_game_finished()
    {
        /* SETUP */
        $scoreBoardService = new ScoreBoardService();
        $scoreBoardService->startGame('Mexico', 'Canada');

        /* EXECUTE */
        $scoreBoardService->finishGame('Mexico', 'Canada');

        /* ASSERT */
        $this->assertCount(0, $scoreBoardService->getGames());
    }

    #[Test]
    public function it_throws_an_exception_if_game_not_started_and_finished()
    {
        /* ASSERT */
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Game not started');

        /* EXECUTE */
        $scoreBoardService = new ScoreBoardService();
        $scoreBoardService->finishGame('Mexico', 'Canada');
    }
}
