<?php

namespace Tests\Feature;

use Tests\TestCase;

class ReadThreadsTest extends TestCase
{
    protected $thread;

    public function setUp()
    {
        parent::setUp();

        $this->thread = create('App\Thread');
    }

    /** @test */
    public function a_user_can_view_all_threads()
    {
        $response = $this->get('/threads')
            ->assertSee(e($this->thread->title));
    }

    /** @test */
    public function a_user_can_read_a_single_thread()
    {
        $response = $this->get($this->thread->path())
            ->assertSee(e($this->thread->title));
    }

    /** @test */
    public function a_user_can_read_replies_that_are_associated_with_a_thread()
    {
        $reply = create('App\Reply', ['thread_id' => $this->thread->id]);

        $response = $this->get($this->thread->path())
            ->assertSee(e($reply->body));
    }

    /** @test */
    public function a_user_can_filter_threads_according_to_a_channel()
    {
        $channel = create('App\Channel');
        $threadInChannel = create('App\Thread', ['channel_id' => $channel->id]);
        $threadNotInChannel = create('App\Thread');

        $this->get("/threads/{$channel->slug}")
            ->assertSee(e($threadInChannel->title))
            ->assertDontSee(e($threadNotInChannel->title));
    }

    /** @test */
    public function a_user_can_filter_threads_by_any_username()
    {
        $user = create('App\User', ['name' => 'JohnDoe']);
        $this->signIn($user);
        
        $threadByJohn = create('App\Thread', ['user_id' => $user->id]);
        $threadNotByJohn = create('App\Thread');

        $this->get('threads?by=' . $user->name)
            ->assertSee(e($threadByJohn->title))
            ->assertDontSee(e($threadNotByJohn->title));
    }

    /** @test */
    public function a_user_can_filter_threads_by_popularity()
    {
        $threadWithTwoReplies = create('App\Thread');
        create('App\Reply', ['thread_id' => $threadWithTwoReplies->id], 2);

        $threadWithThreeReplies = create('App\Thread');
        create('App\Reply', ['thread_id' => $threadWithThreeReplies->id], 3);

        $threadWithNoReplies = $this->thread;

        $response = $this->getJson('threads?popular=1')->json();

        $this->assertEquals([3, 2, 0], array_column($response, 'replies_count'));
    }
    
    /** @test */
    function a_user_can_request_all_replies_for_a_given_thread()
    {
        $thread = create('App\Thread');
        $replies = create('App\Reply', ['thread_id' => $thread->id], 40);

        $response = $this->getJson($thread->path() . '/replies')->json();

        $this->assertCount(20, $response['data']);
        $this->assertEquals(40, $response['total']);
    }
}
