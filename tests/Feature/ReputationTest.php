<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReputationTest extends TestCase
{
    /** @test */
    public function a_user_earns_points_when_they_create_a_thread()
    {
        $thread = create('App\Thread');

        $this->assertEquals(10, $thread->creator->reputation);
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

        $this->assertEquals(2, $user->fresh()->reputation);
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

        $this->assertEquals(52, $user->fresh()->reputation);
    }
}
