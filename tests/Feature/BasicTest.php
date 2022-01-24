<?php

namespace Tests\Feature;

use Tests\TestCase;

class BasicTest extends TestCase
{
    public function test_index_page_loads(): void
    {
        $this->get('/')->assertOk();
    }
}
