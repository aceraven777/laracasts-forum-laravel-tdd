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

        $this->assertFalse($thread->fresh()->pinned, 'Failed asserting that the thread was unpinned.');
    }

    /** @test */
    public function pinned_threads_are_listed_first()
    {
        $threads = create(Thread::class, [], 3);

        $threadToPin = $threads[2];

        $this->signInAdmin();

        $response = $this->getJson(route('threads'))->assertJson([
            'data' => [
                ['id' => $threads[0]->id],
                ['id' => $threads[1]->id],
                ['id' => $threadToPin->id],
            ]
        ]);

        $this->post(route('pinned-threads.store', $threadToPin));

        $response = $this->getJson(route('threads'))->assertJson([
            'data' => [
                ['id' => $threadToPin->id],
                ['id' => $threads[0]->id],
                ['id' => $threads[1]->id],
            ]
        ]);

    }
}