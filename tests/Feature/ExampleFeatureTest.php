<?php

namespace Habib\Master\Tests\Feature;

use Habib\Master\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleFeatureTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
