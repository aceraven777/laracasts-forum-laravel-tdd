<?php

namespace Tests\Unit;

use App\Reply;
use Tests\TestCase;

class ReplyTest extends TestCase
{
    /** @test */
    public function it_has_an_owner()
    {
        $reply = create('App\Reply');

        $this->assertInstanceOf('App\User', $reply->owner);
    }

    /** @test */
    public function it_has_a_thread()
    {
        $reply = create('App\Reply');

        $this->assertInstanceOf('App\Thread', $reply->thread);
    }

    /** @test */
    public function it_knows_if_it_was_just_published()
    {
        $reply = create('App\Reply');

        $this->assertTrue($reply->wasJustPublished());

        $reply->created_at = $reply->created_at->subMonth();
        $reply->save();

        $this->assertFalse($reply->wasJustPublished());
    }

    /** @test */
    public function it_can_detect_all_mentioned_users_in_the_body()
    {
        $reply = new Reply([
            'body' => '@JaneDoe wants to talk to @JohnDoe'
        ]);

        $this->assertEquals(['JaneDoe', 'JohnDoe'], $reply->mentionedUsers());
    }

    /** @test */
    public function it_wraps_mentioned_usernames_in_the_body_within_anchor_tags()
    {
        $user = create('App\User', [
            'name' => 'JaneDoe'
        ]);
        
        $reply = new Reply([
            'body' => "Hello @{$user->name}."
        ]);

        $this->assertEquals(
            'Hello <a href="' . route('profile', [$user]) . '">@'.$user->name.'</a>.',
            $reply->body
        );
    }

    /** @test */
    public function it_knows_if_it_is_the_best_reply()
    {
        $reply = create('App\Reply');

        $this->assertFalse($reply->isBest());

        $reply->thread->best_reply_id = $reply->id;
        $reply->thread->save();

        $this->assertTrue($reply->isBest());
    }
}
