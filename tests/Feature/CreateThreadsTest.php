<?php

namespace Tests\Feature;

use Tests\TestCase;

class CreateThreadsTest extends TestCase
{
    /** @test */
    public function guest_may_not_create_threads()
    {
        $this->withExceptionHandling();

        // Create threads form
        $this->get('/threads/create')
            ->assertRedirect('/login');

        // Post create request
        $this->post('/threads')
            ->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_users_must_first_confirm_their_email_address_before_creating_threads()
    {
        $this->publishThread([], false)
            ->assertRedirect('/threads')
            ->assertSessionHas('flash');
    }

    /** @test */
    public function an_authenticated_user_can_create_new_forum_threads()
    {
        $user = create('App\User', ['confirmed' => true]);
        $this->signIn($user);

        $thread = make('App\Thread');
        $response = $this->post('/threads', $thread->toArray());

        $this->get($response->headers->get('Location'))
            ->assertSee(e($thread->title))
            ->assertSee(e($thread->body));
    }

    /** @test */
    public function a_thread_requires_a_title()
    {
        $this->publishThread(['title' => null])
            ->assertSessionHasErrors('title');
    }

    /** @test */
    public function a_thread_requires_a_body()
    {
        $this->publishThread(['body' => null])
            ->assertSessionHasErrors('body');
    }

    /** @test */
    public function a_thread_requires_a_valid_channel()
    {
        factory('App\Channel', 2)->create();

        $this->publishThread(['channel_id' => null])
            ->assertSessionHasErrors('channel_id');

        $this->publishThread(['channel_id' => 999])
            ->assertSessionHasErrors('channel_id');
    }

    /** @test */
    public function unauthorized_users_may_not_delete_threads()
    {
        $this->withExceptionHandling();

        $thread = create('App\Thread');

        $this->delete($thread->path())
            ->assertRedirect('/login');

        $this->signIn();

        $this->delete($thread->path())
            ->assertStatus(403);
    }

    /** @test */
    public function authorized_users_can_delete_threads()
    {
        $user = create('App\User');
        $this->signIn($user);

        $thread = create('App\Thread', ['user_id' => $user->id]);
        $reply = create('App\Reply', ['thread_id' => $thread->id]);

        $thread_activity_id = $thread->activity->id;
        $reply_activity_id = $reply->activity->id;

        $response = $this->json('DELETE', $thread->path());

        $response->assertStatus(204);

        $this->assertDatabaseMissing('threads', ['id' => $thread->id]);
        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);
        $this->assertDatabaseMissing('activities', ['id' => $thread_activity_id]);
        $this->assertDatabaseMissing('activities', ['id' => $reply_activity_id]);
    }

    protected function publishThread($overrides = [], $confirmedUser = true)
    {
        $user = create('App\User', ['confirmed' => $confirmedUser]);
        $this->withExceptionHandling()->signIn($user);

        $thread = make('App\Thread', $overrides);

        return $this->post('/threads', $thread->toArray());
    }
}
