<?php

namespace Tests\Feature;

use Tests\TestCase;

class SubscribeToThreadsTest extends TestCase
{
    /** @test */
    public function a_user_can_subscribe_to_threads()
    {
        $this->signIn();

        // Given we have a thread
        $thread = create('App\Thread');

        // And the user subscribe to the thread
        $this->post($thread->path() . '/subscriptions');
        
        // Then, each time a reply is left
        $thread->addReply([
            'user_id' => auth()->id(),
            'body' => 'sample reply',
        ]);

        // A notification should be prepared for the user
        // $this->assertCount(1, auth()->user()->notifications);
    }

    /** @test */
    public function a_user_can_unsubscribe_from_threads()
    {
        $this->signIn();

        // Given we have a thread
        $thread = create('App\Thread');

        $thread->subscribe();

        // And the user unsubscribed to the thread
        $this->delete($thread->path() . '/subscriptions');

        $this->assertCount(0, $thread->subscriptions);
    }
}