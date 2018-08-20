<?php

namespace Tests\Feature;

use Tests\TestCase;

class ParticipateInThreadsTest extends TestCase
{
    /** @test */
    public function unauthenticated_users_may_not_add_replies()
    {
        $this->withExceptionHandling()
            ->post('threads/channel/1/replies', [])
            ->assertRedirect('/login');
    }

    /** @test */
    public function an_authenticated_user_may_participate_in_forum_threads()
    {
        $this->signIn();

        $thread = create('App\Thread');

        $reply = make('App\Reply');
        $this->post($thread->path() . '/replies', $reply->toArray());

        $this->assertDatabaseHas('replies', ['body' => $reply->body]);
        $this->assertEquals(1, $thread->fresh()->replies_count);
    }

    /** @test */
    public function a_reply_requires_a_body()
    {
        $this->publishReply(['body' => null])
            ->assertSessionHasErrors('body');
    }

    /** @test */
    public function unauthorized_users_cannot_delete_replies()
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
    public function authorized_users_can_delete_replies()
    {
        $user = create('App\User');
        $this->signIn($user);

        $reply = create('App\Reply', ['user_id' => $user->id]);

        $this->delete("replies/{$reply->id}")->assertStatus(302);

        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);

        $this->assertEquals(0, $reply->thread->fresh()->replies_count);
    }

    /** @test */
    public function unauthorized_users_cannot_update_replies()
    {
        $this->withExceptionHandling();
        
        $reply = create('App\Reply');

        $this->patch('/replies/' . $reply->id, [
            'body' => 'This is a new reply',
        ])->assertRedirect('login');

        $user = create('App\User');
        $this->signIn($user);

        $this->patch('/replies/' . $reply->id, [
            'body' => 'This is a new reply',
        ])->assertStatus(403);
    }

    /** @test */
    public function authorized_users_can_update_replies()
    {
        $user = create('App\User');
        $this->signIn($user);
        $reply = create('App\Reply', ['user_id' => $user->id]);

        $updatedReply = 'This is a new reply';
        $this->patch('/replies/' . $reply->id, [
            'body' => $updatedReply,
        ]);

        $this->assertDatabaseHas('replies', [
            'id' => $reply->id,
            'body' => $updatedReply,
        ]);
    }

    protected function publishReply($overrides)
    {
        $this->withExceptionHandling()->signIn();

        $reply = make('App\Reply', $overrides);

        return $this->post($reply->thread->path() . '/replies', $reply->toArray());
    }
}
