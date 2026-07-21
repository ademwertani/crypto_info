<?php

namespace Tests\Feature;

use App\Models\Platform;
use App\Models\PlatformComparison;
use App\Models\User;
use Database\Seeders\PlatformComparisonSeeder;
use Database\Seeders\PlatformSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PlatformComparisonTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_30_comparisons_with_best_for_highlighted(): void
    {
        $this->seed(PlatformSeeder::class);
        $this->seed(PlatformComparisonSeeder::class);

        $this->assertSame(12, Platform::count());
        $this->assertSame(30, PlatformComparison::count());

        $response = $this->get('/compare-platforms');

        $response->assertOk();
        $response->assertSee('Binance');
        $response->assertSee('Bybit');
        $response->assertSee(__('platforms.best_for_label'));
    }

    public function test_no_fee_data_is_ever_marked_as_verified(): void
    {
        $this->seed(PlatformSeeder::class);

        $this->assertSame(0, Platform::whereNotNull('data_verified_at')->count());
    }

    public function test_reseeding_is_idempotent(): void
    {
        $this->seed(PlatformSeeder::class);
        $this->seed(PlatformComparisonSeeder::class);

        // Run both again — counts must not change.
        $this->seed(PlatformSeeder::class);
        $this->seed(PlatformComparisonSeeder::class);

        $this->assertSame(12, Platform::count());
        $this->assertSame(30, PlatformComparison::count());
    }

    public function test_index_eager_loads_platforms_without_n_plus_one(): void
    {
        $this->seed(PlatformSeeder::class);
        $this->seed(PlatformComparisonSeeder::class);

        DB::enableQueryLog();
        $this->get('/compare-platforms')->assertOk();
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        // With With(['platformA','platformB']) this should be a handful of
        // queries total, not one per row — 30 rows would mean 60+ queries
        // under N+1. A generous ceiling still catches a real regression.
        $this->assertLessThan(15, $queryCount, "Expected eager loading to keep query count low, got {$queryCount}.");
    }

    public function test_no_comparison_pair_exists_in_both_directions(): void
    {
        $this->seed(PlatformSeeder::class);
        $this->seed(PlatformComparisonSeeder::class);

        foreach (PlatformComparison::all() as $comparison) {
            $reversed = PlatformComparison::where('platform_a_id', $comparison->platform_b_id)
                ->where('platform_b_id', $comparison->platform_a_id)
                ->exists();

            $this->assertFalse($reversed, "Found both directions of a pair for comparison #{$comparison->id}.");
        }
    }

    public function test_guests_are_redirected_away_from_the_admin_panel(): void
    {
        $this->get('/admin/platforms')->assertRedirect('/admin/login');
        $this->get('/admin/platform-comparisons')->assertRedirect('/admin/login');
    }

    public function test_authenticated_user_can_view_platform_admin_lists(): void
    {
        $user = User::factory()->create();
        $this->seed(PlatformSeeder::class);
        $this->seed(PlatformComparisonSeeder::class);

        $this->actingAs($user)
            ->get('/admin/platforms')
            ->assertOk()
            ->assertSee('Binance');

        $this->actingAs($user)
            ->get('/admin/platform-comparisons')
            ->assertOk();
    }
}
