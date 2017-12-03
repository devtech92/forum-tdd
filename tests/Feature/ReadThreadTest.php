<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

/**
 * @property mixed thread
 */
class ReadThreadTest extends TestCase
{

    use DatabaseMigrations;

    protected function setUp()
    {
        parent::setUp();

        $this->thread = factory('App\Thread')->create();
    }

    /**
     * @test
     */
    public function a_user_can_browse_threads()
    {
        $this->get('/threads')
            ->assertSee($this->thread->title);

    }

    /**
     * @test
     */
    function a_user_can_view_a_thread()
    {
        $this->get($this->thread->path())
            ->assertSee($this->thread->title);
    }


    /** @test */
    public function a_user_can_filter_thread_by_channel()
    {
        $channel = create('App\Channel');
        $threadInChannel = create('App\Thread', [ 'channel_id' => $channel->id ]);
        $threadNoInChannel = create('App\Thread');

        $this->get('/threads/' . $channel->slug)
            ->assertSee($threadInChannel->title)
            ->assertDontSee($threadNoInChannel->title);
    }

    /** @test */
    function a_user_can_filter_threads_by_any_username()
    {
        $this->signIn(create('App\User', [ 'name' => 'JohnDoe' ]));

        $threadByJohn = create('App\Thread', [ 'user_id' => auth()->id() ]);
        $threadNotByJohn = create('App\Thread');

        $this->get('threads?by=JohnDoe')
            ->assertSee($threadByJohn->title)
            ->assertDontSee($threadNotByJohn->title);
    }

    /** @test */
    function a_user_can_filter_threads_by_popularity()
    {
        $threadWithTwoReplies = create('App\Thread');
        create('App\Reply', [ 'thread_id' => $threadWithTwoReplies->id ], 2);

        $threadWithThreeReplies = create('App\Thread');
        create('App\Reply', [ 'thread_id' => $threadWithThreeReplies->id ], 3);

        $threadWithNoReplies = $this->thread;

        $response = $this->getJson('threads?popular=1')->json();

        $this->assertEquals([ 3, 2, 0 ], array_column($response['data'], 'replies_count'));
    }

    /** @test */
    function a_user_can_request_all_replies_for_a_given_thread()
    {
        $thread = create('App\Thread');
        create('App\Reply', [ 'thread_id' => $thread->id ], 2);

        $response = $this->getJson($thread->path() . '/replies')->json();

        $this->assertCount(2, $response['data']);
        $this->assertEquals(2, $response['total']);
    }

    /** @test */
    function a_user_can_filter_threads_by_those_that_are_unanswered()
    {
        $thread = create('App\Thread');

        create('App\Reply', [ 'thread_id' => $thread->id ]);

        $response = $this->getJson('threads?unanswered=1')->json();

        $this->assertCount(1, $response['data']);
    }

    /** @test */
    function we_record_a_new_visit_each_time_the_thread_is_read()
    {
        $thread = create('App\Thread');

        $this->assertSame(0, $thread->visits);

        $this->call('GET', $thread->path());

        $this->assertEquals(1, $thread->fresh()->visits);
    }
}
