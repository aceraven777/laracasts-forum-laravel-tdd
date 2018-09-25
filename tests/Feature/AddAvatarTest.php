<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AddAvatarTest extends TestCase
{
    /** @test */
    public function on_members_can_add_avatars()
    {
        $this->withExceptionHandling();

        $this->json('POST', '/api/users/SomeUser/avatar')
            ->assertStatus(401);
    }

    /** @test */
    public function a_valid_avatar_must_be_provided()
    {
        $this->withExceptionHandling()->signIn();

        $this->json('POST', '/api/users/' . auth()->user()->name . '/avatar', [
            'avatar' => 'not-an-image'
        ])->assertStatus(422);
    }

    /** @test */
    public function a_user_may_add_an_avatar_to_their_profile()
    {
        $this->signIn();

        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.jpg');
        $this->json('POST', '/api/users/' . auth()->user()->name . '/avatar', [
            'avatar' => $file
        ]);

        $this->assertEquals(asset('storage/avatars/' . $file->hashName()), auth()->user()->fresh()->avatar_path);

        Storage::disk('public')->assertExists('avatars/' . $file->hashName());
    }
}
