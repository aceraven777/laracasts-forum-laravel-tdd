<?php

namespace Tests\Unit;

use App\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    /** @test */
    public function a_user_can_fetch_their_most_recent_reply()
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

        $this->assertEquals(asset('images/avatars/default.jpg'), $user->avatar_path);

        $user->avatar_path = 'avatars/me.jpg';
        $user->save();

        $this->assertEquals(asset('storage/avatars/me.jpg'), $user->avatar_path);
    }

    /** @test */
    public function a_user_must_generate_unique_confirmation_token()
    {
        $email = 'johndoe@gmail.com';

        $user = create('App\User',
            [
                'email' => $email,
                'confirmation_token' => User::generateConfirmationToken($email)
            ]);

        $unique_confirmation_token = User::generateConfirmationToken($email);

        $this->assertNotEquals($user->confirmation_token, $unique_confirmation_token);
    }
}
