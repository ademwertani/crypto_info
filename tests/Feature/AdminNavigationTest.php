<?php

namespace Tests\Feature;

use App\Filament\Resources\AdFormats\AdFormatResource;
use App\Filament\Resources\AdvertiserLeads\AdvertiserLeadResource;
use App\Filament\Resources\ArticleCategories\ArticleCategoryResource;
use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\MoneyPages\MoneyPageResource;
use App\Filament\Resources\NewsPosts\NewsPostResource;
use App\Filament\Resources\PlatformComparisons\PlatformComparisonResource;
use App\Filament\Resources\Platforms\PlatformResource;
use App\Models\AdvertiserLead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNavigationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every admin resource must sit in one of the three navigation groups —
     * this is a regression test for the "can't find X in the dashboard"
     * report: a flat, ungrouped sidebar of 8 resources is easy to lose
     * things in.
     */
    public function test_every_resource_is_assigned_to_a_navigation_group(): void
    {
        $expected = [
            NewsPostResource::class => 'Editorial',
            MoneyPageResource::class => 'Editorial',
            ArticleResource::class => 'Editorial',
            ArticleCategoryResource::class => 'Editorial',
            PlatformResource::class => 'Platforms',
            PlatformComparisonResource::class => 'Platforms',
            AdFormatResource::class => 'Advertise',
            AdvertiserLeadResource::class => 'Advertise',
        ];

        foreach ($expected as $resource => $group) {
            $this->assertSame($group, $resource::getNavigationGroup(), "{$resource} should be in the \"{$group}\" nav group.");
            $this->assertTrue($resource::shouldRegisterNavigation(), "{$resource} should be visible in navigation.");
        }
    }

    public function test_advertiser_leads_nav_badge_reflects_new_lead_count(): void
    {
        $this->assertNull(AdvertiserLeadResource::getNavigationBadge());

        AdvertiserLead::create(['name' => 'Alice', 'email' => 'alice@example.com', 'message' => 'Interested in a banner slot.', 'status' => 'new']);
        AdvertiserLead::create(['name' => 'Bob', 'email' => 'bob@example.com', 'message' => 'Sponsored article inquiry.', 'status' => 'new']);
        AdvertiserLead::create(['name' => 'Cara', 'email' => 'cara@example.com', 'message' => 'Already handled.', 'status' => 'closed']);

        $this->assertSame('2', AdvertiserLeadResource::getNavigationBadge());
        $this->assertSame('danger', AdvertiserLeadResource::getNavigationBadgeColor());
    }

    public function test_authenticated_user_can_load_every_resource_index_page(): void
    {
        $user = User::factory()->create();

        foreach ([
            '/admin/news-posts',
            '/admin/money-pages',
            '/admin/articles',
            '/admin/article-categories',
            '/admin/platforms',
            '/admin/platform-comparisons',
            '/admin/ad-formats',
            '/admin/advertiser-leads',
        ] as $path) {
            $this->actingAs($user)->get($path)->assertOk();
        }
    }

    public function test_dashboard_loads_with_the_site_overview_widget(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertOk();
    }
}
