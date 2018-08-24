<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Notifications\ThreadWasUpdated;
use Illuminate\Support\Facades\Notification;

class ThreadTest extends TestCase
{
    protected $thread;

    public function setUp()
    {
        parent::setUp();

        $this->thread = create('App\Thread');
    }

    /** @test */
    public function a_thread_can_make_a_string_path()
    {
        $this->assertEquals(
            "/threads/{$this->thread->channel->slug}/{$this->thread->id}",
            $this->thread->path()
        );
    }

    /** @test */
    public function a_thread_has_a_creator()
    {
        $this->assertInstanceOf('App\User', $this->thread->creator);
    }

    /** @test */
    public function a_thread_has_replies()
    {
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $this->thread->replies);
    }

    /** @test */
    public function a_thread_can_add_a_reply()
    {
        $this->thread->addReply([
            'user_id' => 1,
            'body' => 'Foobar',
        ]);

        $this->assertCount(1, $this->thread->replies);
    }

    /** @test */
    public function a_thread_notifies_all_registered_subscribers_when_a_reply_is_added()
    {
        Notification::fake();

        $user = create('App\User');
        $this->signIn($user);

        $this->thread->subscribe();

        // Another user leaves a reply
        $this->thread->addReply([
            'user_id' => create('App\User')->id,
            'body' => 'Foobar',
        ]);

        Notification::assertSentTo(
            $user,
            ThreadWasUpdated::class
        );
    }

    /** @test */
    public function a_thread_belongs_to_a_channel()
    {
        $this->assertInstanceOf('App\Channel', $this->thread->channel);
    }

    /** @test */
    public function a_thread_can_be_subscribed_to()
    {
        $user = create('App\User');
        $this->signIn($user);

        $this->thread->subscribe();

        $this->assertEquals(
            1,
            $this->thread->subscriptions()->where('user_id', $user->id)->count()
        );
    }

    /** @test */
    public function a_thread_can_be_subscribed_from()
    {
        $user = create('App\User');
        $this->signIn($user);

        $this->thread->subscribe();
        $this->thread->unsubscribe();

        $this->assertEquals(
            0,
            $this->thread->subscriptions()->where('user_id', $user->id)->count()
        );
    }

    /** @test */
    public function it_knows_if_the_authenticated_user_is_subscribed_to_it()
    {
        $user = create('App\User');
        $this->signIn($user);

        $this->assertFalse($this->thread->isSubscribedTo);

        $this->thread->subscribe();

        $this->assertTrue($this->thread->isSubscribedTo);
    }

    /** @test */
    public function a_subscribed_thread_has_a_user()
    {
        $user = create('App\User');
        $this->signIn($user);

        $this->thread->subscribe();

        $this->assertInstanceOf('App\User', $this->thread->subscriptions()->first()->user);
    }
}
