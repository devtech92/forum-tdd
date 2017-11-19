<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ThreadTest extends TestCase
{
    use DatabaseMigrations;

    protected $thread;

    protected function setUp()
    {
        parent::setUp();

        $this->thread = factory('App\Thread')->create();
    }

    /**
     * @test
     */
    public function s_thread_has_user()
    {
        $this->assertInstanceOf('App\User', $this->thread->user);
    }

    /**
     * @test
     */
    public function s_thread_has_replies()
    {
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $this->thread->replies);
    }

    /**
     * @test
     */
    public function a_thread_can_add_a_reply()
    {
        $this->thread->addReply([
            'body' => 'ABCDEEJKJKJLJL',
            'user_id' => 1,
        ]);

        $this->assertCount(1, $this->thread->replies);
    }

}