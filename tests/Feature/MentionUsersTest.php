<?php

namespace Tests\Feature;

use Tests\TestCase;

class MentionUsersTest extends TestCase
{
    /** @test */
    public function mentioned_users_in_a_reply_are_notified()
    {
        $john = create('App\User', ['name' => 'JohnDoe']);
        $this->signIn($john);

        $jane = create('App\User', ['name' => 'JaneDoe']);
        
        $thread = create('App\Thread');

        $reply = make('App\Reply', [
            'body' => '@' . $jane->name . ' look at this.',
        ]);

        $this->json('post', $reply->thread->path() . '/replies', $reply->toArray());

        $this->assertCount(1, $jane->notifications);
    }

    /** @test */
    public function it_can_fetch_all_mentioned_users_starting_with_the_given_characters()
    {
        $john = create('App\User', ['name' => 'JohnDoe']);
        $john2 = create('App\User', ['name' => 'JohnDoe2']);
        $jane = create('App\User', ['name' => 'JaneDoe']);

        $users = $this->json('get', '/api/users', ['name' => 'john'])->json();

        $this->assertCount(2, $users);
        $this->assertTrue(in_array($john->name, $users));
        $this->assertTrue(in_array($john2->name, $users));
    }
}
