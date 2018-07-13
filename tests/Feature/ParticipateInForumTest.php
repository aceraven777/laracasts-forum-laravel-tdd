<?php

namespace Tests\Feature;

use Tests\TestCase;

class ParticipateInForumTest extends TestCase
{
    /** @test */
    function unauthenticated_users_may_not_add_replies()
    {
        $this->withExceptionHandling()
            ->post('threads/channel/1/replies', [])
            ->assertRedirect('/login');
    }

    /** @test */
    function an_authenticated_user_may_participate_in_forum_threads()
    {
        $this->be($user = create('App\User'));

        $thread = create('App\Thread');

        $reply = make('App\Reply');
        $this->post($thread->path() . '/replies', $reply->toArray());

        $this->get($thread->path())
            ->assertSee($reply->body);
    }

    /** @test */
    function a_reply_requires_a_body()
    {
        $this->publishReply(['body' => null])
            ->assertSessionHasErrors('body');
    }

    /** @test */
    function unauthorized_users_cannot_delete_replies()
    {
        $reply = create('App\Reply');

        $this->withExceptionHandling()
            ->delete("replies/{$reply->id}")
            ->assertRedirect('/login');

        $this->signIn();
        $reply = create('App\Reply');

        $this->delete("replies/{$reply->id}")
            ->assertStatus(403);
    }

    /** @test */
    function authorized_users_can_delete_replies()
    {
        $user = create('App\User');
        $this->signIn($user);

        $reply = create('App\Reply', ['user_id' => $user->id]);

        $this->delete("replies/{$reply->id}")->assertStatus(302);

        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);
    }

    function publishReply($overrides)
    {
        $this->withExceptionHandling()->signIn();

        $reply = make('App\Reply', $overrides);

        return $this->post($reply->thread->path() . '/replies', $reply->toArray());
    }
}
