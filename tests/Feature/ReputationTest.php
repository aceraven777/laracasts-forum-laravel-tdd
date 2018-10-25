<?php

namespace Tests\Feature;

use App\Reputation;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReputationTest extends TestCase
{
    /** @test */
    public function a_user_earns_points_when_they_create_a_thread()
    {
        $thread = create('App\Thread');

        $this->assertEquals(Reputation::THREAD_WAS_PUBLISHED, $thread->creator->reputation);
    }

    /** @test */
    public function a_user_earns_points_when_they_reply_to_a_thread()
    {
        $user = create('App\User');

        $thread = create('App\Thread');

        $thread->addReply([
            'user_id' => $user->id,
            'body' => 'Here is a reply.',
        ]);

        $this->assertEquals(Reputation::REPLY_POSTED, $user->fresh()->reputation);
    }

    /** @test */
    public function a_user_earns_points_when_their_reply_is_mark_as_best()
    {
        $user = create('App\User');

        $thread = create('App\Thread');

        $reply = $thread->addReply([
            'user_id' => $user->id,
            'body' => 'Here is a reply.',
        ]);

        $thread->markBestReply($reply);

        $this->assertEquals(Reputation::REPLY_POSTED + Reputation::BEST_REPLY_AWARDED, $user->fresh()->reputation);
    }
}
