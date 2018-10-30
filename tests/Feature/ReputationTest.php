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
    public function a_user_lose_points_when_they_delete_a_thread()
    {
        $this->signIn();

        $thread = create('App\Thread', ['user_id' => auth()->id()]);
        
        $this->assertEquals(Reputation::THREAD_WAS_PUBLISHED, $thread->creator->reputation);

        $this->delete($thread->path());

        $this->assertEquals(0, $thread->creator->fresh()->reputation);
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
    public function a_user_loses_points_when_their_reply_to_a_thread_is_deleted()
    {
        $this->signIn();

        $reply = create('App\Reply', ['user_id' => auth()->id()]);

        $this->assertEquals(Reputation::REPLY_POSTED, $reply->owner->reputation);

        $this->delete("/replies/{$reply->id}");

        $this->assertEquals(0, $reply->owner->fresh()->reputation);
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

    /** @test */
    public function a_user_earns_points_when_their_reply_is_favorited()
    {
        $this->signIn();

        $thread = create('App\Thread');

        $reply = $thread->addReply([
            'user_id' => auth()->id(),
            'body' => 'Here is a reply.',
        ]);

        $this->post("/replies/{$reply->id}/favorites");

        $this->assertEquals(Reputation::REPLY_POSTED + Reputation::REPLY_FAVORITED, $reply->owner->fresh()->reputation);
    }

    /** @test */
    public function a_user_loses_points_when_their_favorited_reply_is_unfavorited()
    {
        $this->signIn();

        $reply = create('App\Reply', ['user_id' => auth()->id()]);

        $this->post("/replies/{$reply->id}/favorites");

        $this->assertEquals(Reputation::REPLY_POSTED + Reputation::REPLY_FAVORITED, $reply->owner->fresh()->reputation);

        $this->delete("/replies/{$reply->id}/favorites");

        $this->assertEquals(Reputation::REPLY_POSTED + Reputation::REPLY_FAVORITED - Reputation::REPLY_FAVORITED, $reply->owner->fresh()->reputation);
    }
}
