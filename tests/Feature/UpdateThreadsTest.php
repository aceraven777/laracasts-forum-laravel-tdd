<?php

namespace Tests\Feature;

use Tests\TestCase;

class UpdateThreadsTest extends TestCase
{
    /** @test */
    public function a_thread_requires_a_title_and_body_to_be_updated()
    {
        $this->withExceptionHandling()->signIn();

        $thread = create('App\Thread', ['user_id' => auth()->id()]);

        $this->patch($thread->path(), [
            'title' => 'Changed',
        ])->assertSessionHasErrors('body');

        $this->patch($thread->path(), [
            'body' => 'Changed body.',
        ])->assertSessionHasErrors('title');
    }

    /** @test */
    public function guest_may_not_update_threads()
    {
        $this->withExceptionHandling();
        
        $thread = create('App\Thread');

        $this->patch($thread->path(), [])->assertRedirect(route('login'));
    }

    /** @test */
    public function unauthorized_users_may_not_update_threads()
    {
        $this->withExceptionHandling()->signIn();

        $thread = create('App\Thread', ['user_id' => create('App\User')->id]);

        $this->patch($thread->path(), [])->assertStatus(403);
    }

    /** @test */
    public function a_thread_can_be_updated_by_its_creator()
    {
        $this->signIn();

        $thread = create('App\Thread', ['user_id' => auth()->id()]);

        $this->patch($thread->path(), [
            'title' => 'Changed',
            'body' => 'Changed body.',
        ]);

        $thread = $thread->fresh();

        $this->assertEquals('Changed', $thread->title);
        $this->assertEquals('Changed body.', $thread->body);
    }
}
