<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProfilesTest extends TestCase
{
    /** @test */
    public function a_user_has_a_profile()
    {
        $user = create('App\User');

        $response = $this->get("/profiles/{$user->name}")
            ->assertSee($user->name);
    }

    /** @test */
    public function profiles_display_all_threads_created_by_the_associated_user()
    {
        $this->signIn();
        
        $user = auth()->user();
        $thread = create('App\Thread', ['user_id' => $user->id]);

        $response = $this->get("/profiles/{$user->name}")
            ->assertSee($thread->title)
            ->assertSee($thread->body);
    }
}
