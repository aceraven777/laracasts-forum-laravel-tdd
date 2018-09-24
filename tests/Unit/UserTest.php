<?php

namespace Tests\Unit;

use Tests\TestCase;

class UserTest extends TestCase
{
    /** @test */
    public function a_user_can_fet_their_most_recent_reply()
    {
        $user = create('App\User');
        $reply = create('App\Reply', [
            'user_id' => $user->id
        ]);

        $this->assertEquals($reply->id, $user->lastReply->id);
    }

    /** @test */
    public function a_user_can_determine_their_avatar_path()
    {
        $user = create('App\User');

        $this->assertEquals(asset('storage/avatars/default.jpg'), $user->avatar());

        $user->avatar_path = 'avatars/me.jpg';
        $user->save();

        $this->assertEquals(asset('storage/avatars/me.jpg'), $user->avatar());
    }
}
