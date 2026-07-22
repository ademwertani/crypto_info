<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AdvertiserLeads\AdvertiserLeadResource;
use App\Models\AdvertiserLead;
use App\Models\Article;
use App\Models\Cryptocurrency;
use App\Models\MoneyPage;
use App\Models\NewsPost;
use App\Models\Platform;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** First thing an admin sees — a single-glance count of everything the site is currently serving. */
class SiteOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $newLeads = AdvertiserLead::where('status', 'new')->count();

        return [
            Stat::make('Cryptocurrencies tracked', Cryptocurrency::count())
                ->description('Synced every 10 min from CoinGecko')
                ->color('gray'),

            Stat::make('Money pages', MoneyPage::published()->count().' / '.MoneyPage::count())
                ->description('Published / total guides & reviews')
                ->color(MoneyPage::where('status', 'draft')->count() > 0 ? 'warning' : 'success'),

            Stat::make('News posts', NewsPost::published()->count().' / '.NewsPost::count())
                ->description('Published / total')
                ->color('success'),

            Stat::make('Blog articles', Article::published()->count().' / '.Article::count())
                ->description('Published / total')
                ->color('success'),

            Stat::make('Platforms listed', Platform::count())
                ->description('Exchanges & wallets in comparisons')
                ->color('gray'),

            Stat::make('New advertiser leads', $newLeads)
                ->description($newLeads > 0 ? 'Awaiting a reply' : 'All caught up')
                ->color($newLeads > 0 ? 'danger' : 'success')
                ->url(AdvertiserLeadResource::getUrl('index')),
        ];
    }
}
