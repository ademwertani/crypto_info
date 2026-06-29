<?php

namespace Tests\Feature;

use Tests\TestCase;

class NewsPageTest extends TestCase
{
    public function test_news_routes_are_no_longer_available(): void
    {
        $response = $this->get('/news');
        $response->assertNotFound();
    }
}
