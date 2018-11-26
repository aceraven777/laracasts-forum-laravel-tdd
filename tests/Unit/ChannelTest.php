<?php

namespace Tests\Unit;

use Tests\TestCase;

class ChannelTest extends TestCase
{
    /** @test */
    public function a_channel_consists_of_threads()
    {
        $channel = create('App\Channel');
        $thread = create('App\Thread', ['channel_id' => $channel->id]);

        $this->assertTrue($channel->threads->contains($thread));
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $channel->threads);
    }

    /** @test */
    public function a_channel_can_be_archived()
    {
        $channel = create('App\Channel');

        $this->assertFalse($channel->archived);

        $channel->archive();

        $this->assertTrue($channel->archived);
    }
}
