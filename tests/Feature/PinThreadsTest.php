<?php

namespace Tests\Feature;

use App\Channel;
use App\Thread;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PinThreadsTest extends TestCase
{
    /** @test */
    public function administrators_can_pin_threads()
    {
        $this->signInAdmin();

        $thread = create('App\Thread');

        $this->post(route('pinned-threads.store', $thread));

        $this->assertTrue($thread->fresh()->pinned, 'Failed asserting that the thread was pinned.');
    }

    /** @test */
    public function administrators_can_unpin_threads()
    {
        $this->signInAdmin();

        $thread = create('App\Thread', ['pinned' => true]);

        $this->delete(route('pinned-threads.destroy', $thread));

        $this->assertFalse($thread->fresh()->pinned, 'Failed asserting that the thread was unlocked.');
    }

    /** @test */
    public function pinned_threads_are_listed_first()
    {
        $channel = create(Channel::class, [
            'name' => 'PHP',
            'slug' => 'php'
        ]);

        $thread1 = create(Thread::class, ['channel_id' => $channel->id]);
        $thread2 = create(Thread::class, ['channel_id' => $channel->id]);
        $threadToPin = create(Thread::class, ['channel_id' => $channel->id]);

        $this->signInAdmin();

        $response = $this->getJson(route('threads'));
        $response->assertJson([
            'data' => [
                ['id' => $thread1->id],
                ['id' => $thread2->id],
                ['id' => $threadToPin->id],
            ]
        ]);

        $this->post(route('pinned-threads.store', $threadToPin));

        $response = $this->getJson(route('threads'));
        $response->assertJson([
            'data' => [
                ['id' => $threadToPin->id],
                ['id' => $thread1->id],
                ['id' => $thread2->id],
            ]
        ]);

    }
}