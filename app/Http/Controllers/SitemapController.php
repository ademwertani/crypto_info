<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Cryptocurrency;
use App\Models\NewsPost;
use Illuminate\Http\Response;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    /**
     * Build the sitemap directly from routes/DB instead of crawling the live
     * site. The previous crawler-based approach (SitemapGenerator::create())
     * made the app fetch its own homepage and follow links to discover URLs,
     * which routinely hit PHP's max_execution_time and produced an empty
     * sitemap (see storage/logs/laravel.log — "Maximum execution time of 30
     * seconds exceeded"). This version is instant and always accurate.
     */
    public function index(): Response
    {
        $path = storage_path('app/public/sitemap.xml');

        if (! file_exists($path) || filemtime($path) < now()->subHours(12)->timestamp) {
            $this->build()->writeToFile($path);
        }

        return response(file_get_contents($path), 200, ['Content-Type' => 'application/xml']);
    }

    private function build(): Sitemap
    {
        $sitemap = Sitemap::create()
            ->add(Url::create(route('crypto.index'))->setPriority(1.0)->setChangeFrequency(Url::CHANGE_FREQUENCY_HOURLY))
            ->add(Url::create(route('market.gainers'))->setPriority(0.8)->setChangeFrequency(Url::CHANGE_FREQUENCY_HOURLY))
            ->add(Url::create(route('market.losers'))->setPriority(0.8)->setChangeFrequency(Url::CHANGE_FREQUENCY_HOURLY))
            ->add(Url::create(route('market.trending'))->setPriority(0.8)->setChangeFrequency(Url::CHANGE_FREQUENCY_HOURLY))
            ->add(Url::create(route('market.fear-greed'))->setPriority(0.7)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create(route('market.bitcoin-dominance'))->setPriority(0.7)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create(route('market.global-cap'))->setPriority(0.7)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create(route('crypto.compare.chooser'))->setPriority(0.6)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY))
            ->add(Url::create(route('blog.index'))->setPriority(0.8)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create(route('news.index'))->setPriority(0.8)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create(route('pages.about'))->setPriority(0.5)->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY))
            ->add(Url::create(route('pages.privacy'))->setPriority(0.2)->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY))
            ->add(Url::create(route('pages.terms'))->setPriority(0.2)->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY));

        Cryptocurrency::query()->select('slug', 'updated_at')->orderBy('market_cap_rank')
            ->chunk(500, function ($coins) use ($sitemap) {
                foreach ($coins as $coin) {
                    $sitemap->add(
                        Url::create(route('crypto.show', $coin->slug))
                            ->setLastModificationDate($coin->updated_at ?? now())
                            ->setPriority(0.6)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_HOURLY)
                    );
                }
            });

        Article::query()->published()->select('slug', 'updated_at')
            ->chunk(500, function ($articles) use ($sitemap) {
                foreach ($articles as $article) {
                    $sitemap->add(
                        Url::create(route('blog.show', $article->slug))
                            ->setLastModificationDate($article->updated_at ?? now())
                            ->setPriority(0.6)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    );
                }
            });

        NewsPost::query()->published()->select('slug', 'updated_at')
            ->chunk(500, function ($newsPosts) use ($sitemap) {
                foreach ($newsPosts as $newsPost) {
                    $sitemap->add(
                        Url::create(route('news.show', $newsPost->slug))
                            ->setLastModificationDate($newsPost->updated_at ?? now())
                            ->setPriority(0.6)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    );
                }
            });

        return $sitemap;
    }
}
