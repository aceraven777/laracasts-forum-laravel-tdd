<?php

namespace Tests\Unit;

use App\Activity;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    /** @test */
    public function it_records_activity_when_a_thread_is_created()
    {
        $this->signIn();
        $user_id = auth()->id();
        $thread = create('App\Thread', ['user_id' => $user_id]);

        $this->assertDatabaseHas('activities', [
            'subject_id' => $thread->id,
            'subject_type' => 'App\Thread',
            'user_id' => $user_id,
            'type' => 'created_thread',
        ]);

        $activity = Activity::first();

        $this->assertEquals($activity->subject->id, $thread->id);
    }

    /** @test */
    public function it_records_activity_when_a_reply_is_created()
    {
        $this->signIn();
        $user_id = auth()->id();
        $reply = create('App\Reply', ['user_id' => $user_id]);

        $this->assertDatabaseHas('activities', [
            'subject_id' => $reply->id,
            'subject_type' => 'App\Reply',
            'user_id' => $user_id,
            'type' => 'created_reply',
        ]);

        $activity = Activity::orderBy('id', 'DESC')->first();

        $this->assertEquals($activity->subject->id, $reply->id);
    }
}
