<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProfilesTest extends TestCase
{
    /** @test */
    public function a_user_has_a_profile()
    {
        $user = create(\App\User::class);

        $response = $this->getJson("/profiles/{$user->username}")->json();

        $this->assertEquals($response['profileUser']['name'], $user->name);
    }
}