<?php

namespace Tests\Feature;

use Tests\TestCase;

class BestReplyTest extends TestCase
{
    /** @test */
    public function a_thread_creator_may_mark_any_reply_as_the_best_reply()
    {
        $user = create('App\User');
        $this->signIn($user);

        $thread = create('App\Thread', ['user_id' => $user->id]);
        
        $replies = create('App\Reply', ['thread_id' => $thread->id], 2);

        $this->assertFalse($replies[1]->isBest());

        $this->postJson(route('best-replies.store', ['reply' => $replies[1]->id]));

        $this->assertTrue($replies[1]->fresh()->isBest());
    }

    /** @test */
    public function only_the_thread_creator_may_mark_a_reply_as_best()
    {
        $this->withExceptionHandling();

        $user = create('App\User');
        $this->signIn($user);

        $thread = create('App\Thread', ['user_id' => $user->id]);
        
        $replies = create('App\Reply', ['thread_id' => $thread->id], 2);
        
        $user = create('App\User');
        $this->signIn($user);

        $this->postJson(route('best-replies.store', ['reply' => $replies[1]->id]))
            ->assertStatus(403);

        $this->assertFalse($replies[1]->fresh()->isBest());
    }

    /** @test */
    public function if_a_best_reply_is_deleted_then_the_thread_is_properly_updated_to_reflect_that()
    {
        $user = create('App\User');
        $this->signIn($user);

        $reply = create('App\Reply', ['user_id' => $user->id]);

        $reply->thread->markBestReply($reply);

        $this->delete(route('replies.destroy', $reply));

        $this->assertNull($reply->thread->fresh()->best_reply_id);
    }
}
