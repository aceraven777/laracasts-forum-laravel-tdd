<?php

namespace Tests\Feature\Admin;

use App\User;
use App\Channel;
use Tests\TestCase;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class ChannelAdministrationTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    /** @test */
    public function an_administrator_can_access_the_channel_administration_section()
    {
        $administrator = $this->signInAdmin();

        $this->get(route('admin.channels.index'))
             ->assertStatus(Response::HTTP_OK);
    }

    /** @test */
    public function non_administrators_cannot_access_the_channel_administration_section()
    {
        $regularUser = create('App\User');

        $this->signIn($regularUser)
             ->get(route('admin.channels.index'))
             ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->signIn($regularUser)
             ->get(route('admin.channels.create'))
             ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function an_administrator_can_create_a_channel()
    {
        $response = $this->createChannel([
            'name' => 'php',
            'description' => 'This is the channel for discussing all things PHP.',
        ]);

        $this->get($response->headers->get('Location'))
             ->assertSee('php')
             ->assertSee('This is the channel for discussing all things PHP.');
    }

    /** @test */
    public function an_administrator_can_edit_an_existing_channel()
    {
        $this->signInAdmin();

        $channel = create('App\Channel');

        $updated_data = [
            'name' => 'altered',
            'description' => 'altered channel description'
        ] + $channel->toArray();

        $this->patch(
            route('admin.channels.update', ['channel' => $channel->slug]), 
            $updated_data
        );

        $this->get(route('admin.channels.index'))
            ->assertSee($updated_data['name'])
            ->assertSee($updated_data['description']);
    }

    /** @test */
    public function an_administrator_can_mark_an_existing_channel_as_archived()
    {
        $this->signInAdmin();

        $channel = create('App\Channel');

        $this->assertFalse($channel->archived);

        $updated_data = [
            'archived' => true
        ] + $channel->toArray();

        $this->patch(
            route('admin.channels.update', ['channel' => $channel->slug]),
            $updated_data
        );

        $this->assertTrue($channel->fresh()->archived);
    }
    
    /** @test */
    public function archive_channel_should_not_influence_existing_thread()
    {
        $this->signInAdmin();
        $channel = create('App\Channel');
        $thread = create('App\Thread', ['channel_id' => $channel->id]);
        $path = $thread->path();

        $channel->archived = true;
        $channel->save();

        $this->assertEquals($path, $thread->fresh()->path());
    }

    /** @test */
    public function a_channel_requires_a_name()
    {
        $this->createChannel(['name' => null])
             ->assertSessionHasErrors('name');
    }

    /** @test */
    public function a_channel_requires_a_description()
    {
        $this->createChannel(['description' => null])
             ->assertSessionHasErrors('description');
    }

    protected function createChannel($overrides = [])
    {
        $administrator = $this->signInAdmin();

        $channel = make(Channel::class, $overrides);

        return $this->post(route('admin.channels.store'), $channel->toArray());
    }

}