<?php

namespace Tests\Feature;

use App\Filament\Resources\NewsPosts\Pages\CreateNewsPost;
use App\Filament\Resources\NewsPosts\Pages\EditNewsPost;
use App\Filament\Resources\NewsPosts\Pages\ListNewsPosts;
use App\Models\NewsPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NewsPostAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_away_from_the_admin_panel(): void
    {
        $this->get('/admin/news-posts')->assertRedirect('/admin/login');
    }

    public function test_authenticated_user_can_view_the_news_posts_list(): void
    {
        $user = User::factory()->create();

        NewsPost::create([
            'title'   => 'Admin List Example',
            'slug'    => 'admin-list-example',
            'content' => '<p>Body</p>',
            'status'  => 'draft',
        ]);

        $this->actingAs($user)
            ->get('/admin/news-posts')
            ->assertOk()
            ->assertSee('Admin List Example');
    }

    public function test_creating_a_published_post_without_touching_the_date_still_shows_it_on_the_site(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateNewsPost::class)
            ->fillForm([
                'title' => 'Bitcoin Breaks New Ground',
                'status' => 'published',
                'content' => '<p>Body</p>',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $post = NewsPost::where('title', 'Bitcoin Breaks New Ground')->firstOrFail();

        $this->assertNotNull($post->published_at);
        $this->assertTrue($post->published_at->lessThanOrEqualTo(now()));

        $this->get('/news')->assertSee('Bitcoin Breaks New Ground');
    }

    public function test_editing_a_post_updates_it_on_the_site(): void
    {
        $user = User::factory()->create();
        $post = NewsPost::create([
            'title'        => 'Original Title',
            'slug'         => 'original-title',
            'content'      => '<p>Original body</p>',
            'status'       => 'published',
            'published_at' => now()->subDay(),
        ]);

        Livewire::actingAs($user)
            ->test(EditNewsPost::class, ['record' => $post->getRouteKey()])
            ->fillForm(['title' => 'Updated Title'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Updated Title', $post->fresh()->title);
        $this->get('/news/'.$post->slug)->assertSee('Updated Title');
    }

    public function test_deleting_a_post_removes_it_from_the_site(): void
    {
        $user = User::factory()->create();
        $post = NewsPost::create([
            'title'        => 'To Be Deleted',
            'slug'         => 'to-be-deleted',
            'content'      => '<p>Body</p>',
            'status'       => 'published',
            'published_at' => now()->subDay(),
        ]);

        Livewire::actingAs($user)
            ->test(EditNewsPost::class, ['record' => $post->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($post);
        $this->get('/news/'.$post->slug)->assertNotFound();
    }

    public function test_publish_and_unpublish_table_actions_toggle_site_visibility(): void
    {
        $user = User::factory()->create();
        $post = NewsPost::create([
            'title'   => 'Draft Toggle Example',
            'slug'    => 'draft-toggle-example',
            'content' => '<p>Body</p>',
            'status'  => 'draft',
        ]);

        Livewire::actingAs($user)
            ->test(ListNewsPosts::class)
            ->callTableAction('publish', $post);

        $this->assertSame('published', $post->fresh()->status);
        $this->assertNotNull($post->fresh()->published_at);
        $this->get('/news/'.$post->slug)->assertOk()->assertSee('Draft Toggle Example');

        Livewire::actingAs($user)
            ->test(ListNewsPosts::class)
            ->callTableAction('unpublish', $post);

        $this->assertSame('draft', $post->fresh()->status);
        $this->get('/news/'.$post->slug)->assertNotFound();
    }
}
