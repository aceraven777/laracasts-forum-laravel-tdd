<?php

namespace Tests\Feature;

use App\Thread;
use Tests\TestCase;

class SearchTest extends TestCase
{
    /** @test */
    public function a_user_can_search_threads()
    {
        $this->markTestSkipped('must be revisited.');
        
        config(['scout.driver' => 'algolia']);

        $search = 'foobar';

        create('App\Thread', [], 2);
        create('App\Thread', ['body' => "A thread with the {$search} term."], 2);

        do {
            sleep(.25);

            $results = $this->getJson("/threads/search?q={$search}")->json();
        } while(empty($results));

        $this->assertCount(2, $results['data']);
    }

    protected function tearDown()
    {
        if (config('scout.driver')) {
            Thread::all()->unsearchable();
        }
    }
}
