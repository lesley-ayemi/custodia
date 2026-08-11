<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Sanctum only treats a request as "from the frontend" (and boots the
        // session) when it carries a Referer/Origin matching a stateful domain.
        $this->withHeader('Referer', config('app.url'));
    }
}
