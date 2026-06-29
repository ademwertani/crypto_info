<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoMetadataTest extends TestCase
{
    public function test_homepage_renders_comprehensive_seo_tags(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('meta name="robots"', false);
        $response->assertSee('property="og:locale"', false);
        $response->assertSee('property="og:image:alt"', false);
        $response->assertSee('hreflang="x-default"', false);
    }
}
