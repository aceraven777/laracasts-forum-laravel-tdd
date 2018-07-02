<?php

namespace Tests\Unit;

use App\Activity;
use Carbon\Carbon;
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

    /** @test */
    public function it_fetches_a_activity_feed_for_any_user()
    {
        $this->signIn();
        $user = auth()->user();
        $user_id = $user->id;

        // Create 2 threads
        create('App\Thread', ['user_id' => $user_id], 2);
    
        // Update 1 thread to week ago
        $activity_week_ago = $user->activities()->first();
        $activity_week_ago->created_at = Carbon::now()->subWeek();
        $activity_week_ago->save();

        $feed = Activity::feed($user);

        $this->assertTrue($feed->keys()->contains(
            Carbon::now()->format('Y-m-d')
        ));

        $this->assertTrue($feed->keys()->contains(
            Carbon::now()->subWeek()->format('Y-m-d')
        ));
    }
}
