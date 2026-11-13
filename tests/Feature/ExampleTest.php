<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Replaces the default example test (which expected Laravel's welcome page at "/").
 */
class ExampleTest extends TestCase
{
    public function test_the_front_door_sends_people_on_to_the_home_route(): void
    {
        $this->get('/')->assertRedirect('/home');
    }
}
