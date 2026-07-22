<?php

namespace Tests\Feature;

use App\Filament\Resources\ArticleCategories\Pages\CreateArticleCategory;
use App\Filament\Resources\ArticleCategories\Pages\EditArticleCategory;
use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Cryptocurrency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ArticleAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_away_from_the_admin_panel(): void
    {
        $this->get('/admin/articles')->assertRedirect('/admin/login');
        $this->get('/admin/article-categories')->assertRedirect('/admin/login');
    }

    public function test_a_full_article_can_be_created_from_filament_and_appears_on_the_site(): void
    {
        $user = User::factory()->create();
        $category = ArticleCategory::create(['name' => 'Guides', 'slug' => 'guides']);
        $bitcoin = Cryptocurrency::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'slug' => 'bitcoin']);

        Livewire::actingAs($user)
            ->test(CreateArticle::class)
            ->fillForm([
                'title' => 'Understanding Market Cycles',
                'article_category_id' => $category->id,
                'status' => 'published',
                'excerpt' => 'A beginner-friendly look at crypto market cycles.',
                'sections' => [
                    ['html' => '<h2>What Is a Market Cycle?</h2><p>Details here.</p>'],
                    ['html' => '<h2>Why It Matters</h2><p>More details.</p>'],
                ],
                'related_coin_slugs' => [$bitcoin->slug],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $article = Article::where('title', 'Understanding Market Cycles')->firstOrFail();

        $this->assertNotSame('', $article->slug);
        $this->assertSame($category->id, $article->article_category_id);
        $this->assertCount(2, $article->sections);
        $this->assertSame([$bitcoin->slug], $article->related_coin_slugs);

        $response = $this->get('/blog/'.$article->slug);
        $response->assertOk();
        $response->assertSee('Understanding Market Cycles');
        $response->assertSee('What Is a Market Cycle?', false);
        $response->assertSee('Bitcoin');
    }

    public function test_publish_and_unpublish_table_actions_toggle_site_visibility(): void
    {
        $user = User::factory()->create();
        $article = Article::create([
            'title' => 'Draft Toggle Example',
            'slug' => 'draft-toggle-example',
            'sections' => ['<p>Body</p>'],
            'status' => 'draft',
        ]);

        Livewire::actingAs($user)
            ->test(ListArticles::class)
            ->callTableAction('publish', $article);

        $this->assertSame('published', $article->fresh()->status);
        $this->assertNotNull($article->fresh()->published_at);
        $this->get('/blog/'.$article->slug)->assertOk();

        Livewire::actingAs($user)
            ->test(ListArticles::class)
            ->callTableAction('unpublish', $article);

        $this->assertSame('draft', $article->fresh()->status);
        $this->get('/blog/'.$article->slug)->assertNotFound();
    }

    public function test_editing_an_article_updates_it_on_the_site(): void
    {
        $user = User::factory()->create();
        $article = Article::create([
            'title' => 'Original Title',
            'slug' => 'original-article-title',
            'sections' => ['<p>Body</p>'],
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        Livewire::actingAs($user)
            ->test(EditArticle::class, ['record' => $article->getRouteKey()])
            ->fillForm(['title' => 'Updated Title'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Updated Title', $article->fresh()->title);
        $this->get('/blog/'.$article->slug)->assertSee('Updated Title');
    }

    public function test_an_article_category_can_be_created_and_edited(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateArticleCategory::class)
            ->fillForm(['name' => 'Security', 'description' => 'Wallet & account security guides.'])
            ->call('create')
            ->assertHasNoFormErrors();

        $category = ArticleCategory::where('name', 'Security')->firstOrFail();
        $this->assertSame('security', $category->slug);

        Livewire::actingAs($user)
            ->test(EditArticleCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm(['name' => 'Security & Custody'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Security & Custody', $category->fresh()->name);
    }
}
