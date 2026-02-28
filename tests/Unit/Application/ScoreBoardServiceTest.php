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
}
