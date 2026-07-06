<?php

namespace Tests\Feature;

use Tests\TestCase;

class RemovedUserNewsletterRoutesTest extends TestCase
{
    public function test_user_and_newsletter_routes_are_removed(): void
    {
        foreach ([
            '/login',
            '/register',
            '/dashboard',
            '/profile',
            '/watchlist',
            '/newsletter/subscribe',
        ] as $uri) {
            $this->call($uri === '/newsletter/subscribe' ? 'POST' : 'GET', $uri)
                ->assertNotFound();
        }
    }
}
