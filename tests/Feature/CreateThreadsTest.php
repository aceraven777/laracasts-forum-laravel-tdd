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
        $this->get(route('threads.create'))
            ->assertRedirect(route('login'));

        // Post create request
        $this->post(route('threads.store'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function new_users_must_first_confirm_their_email_address_before_creating_threads()
    {
        $user = factory('App\User')->states('unconfirmed')->create();

        $this->signIn($user);

        // Can user go to the create test form
        $this->get(route('threads.create'))
            ->assertRedirect(route('threads'))
            ->assertSessionHas('flash', 'You must first confirm your email address.');

        $thread = make('App\Thread');

        $this->post(route('threads.store'), $thread->toArray())
            ->assertRedirect(route('threads'))
            ->assertSessionHas('flash', 'You must first confirm your email address.');
    }

    /** @test */
    public function a_user_can_create_new_forum_threads()
    {
        $user = create('App\User');
        $this->signIn($user);

        $thread = make('App\Thread');
        $response = $this->post(route('threads.store'), $thread->toArray());

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
    public function a_thread_requires_a_unique_slug()
    {
        $duplicate_title = 'Foo title';

        $user = create('App\User');
        $this->signIn($user);
        
        $thread = create('App\Thread', ['title' => $duplicate_title]);

        $thread= $thread->fresh();

        $this->publishThread(['title' => $duplicate_title]);

        $this->assertDatabaseHas('threads', [
            'slug' => $thread->slug . '-2',
        ]);

        $this->publishThread(['title' => $duplicate_title]);

        $this->assertDatabaseHas('threads', [
            'slug' => $thread->slug . '-3',
        ]);
    }

    /** @test */
    public function unauthorized_users_may_not_delete_threads()
    {
        $this->withExceptionHandling();

        $thread = create('App\Thread');

        $this->delete($thread->path())
            ->assertRedirect(route('login'));

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

    protected function publishThread($overrides = [])
    {
        $user = create('App\User');
        $this->withExceptionHandling()->signIn($user);

        $thread = make('App\Thread', $overrides);

        return $this->post(route('threads.store'), $thread->toArray());
    }
}
