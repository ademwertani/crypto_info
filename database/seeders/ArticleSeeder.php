<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;

/**
 * ⚠️ EXAMPLE CONTENT — FOR DEMONSTRATION ONLY.
 *
 * These 8 articles exist to show a working, SEO-structured blog with real
 * internal linking, ad placement and disclaimers wired up end to end.
 * Before going to production:
 *   - Have a human editor review, rewrite or replace every article below.
 *   - Add real cover images (cover_image_url) — none are set here on purpose,
 *     since no real, licensed images were available to attach.
 *   - Re-check facts and figures for accuracy at time of publication.
 *   - Adjust published_at dates to real publication dates.
 */
class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $beginner = ArticleCategory::where('slug', 'beginner-guides')->first();
        $security = ArticleCategory::where('slug', 'security')->first();
        $market   = ArticleCategory::where('slug', 'market-analysis')->first();

        $articles = [
            [
                'article_category_id' => $beginner?->id,
                'title'       => "A Beginner's Guide to Understanding Cryptocurrency",
                'slug'        => 'beginner-guide-understanding-cryptocurrency',
                'excerpt'     => "New to crypto? This guide breaks down what cryptocurrency actually is, how blockchains work, and the key terms you'll run into as a beginner.",
                'meta_title'  => "Beginner's Guide to Understanding Cryptocurrency",
                'meta_description' => "Learn what cryptocurrency is, how blockchain technology works, and the key terms every beginner should know before exploring the crypto market.",
                'related_coin_slugs' => ['bitcoin', 'ethereum'],
                'published_days_ago' => 3,
                'sections' => [
                    '<h2 class="text-lg font-bold text-white mt-0 mb-3">What Is Cryptocurrency?</h2>
<p class="mb-3">Cryptocurrency is a form of digital money that exists only electronically and is secured using cryptography rather than being issued by a central bank. Bitcoin, launched in 2009, was the first cryptocurrency to work this way, and thousands of others have since been created, each with different goals and technical designs.</p>
<p class="mb-3">Unlike the money in a traditional bank account, most cryptocurrencies are designed to run on decentralized networks — meaning no single company or government controls the ledger that records who owns what.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">How Does a Blockchain Work?</h2>
<p class="mb-3">A blockchain is essentially a shared record book (a "ledger") that is copied across thousands of computers around the world instead of being stored in one place. Every new set of transactions is bundled into a "block," checked by the network, and then permanently linked to the previous block — forming a chain.</p>
<ul class="list-disc pl-5 space-y-1.5 mb-3">
<li>Transactions are grouped into blocks and verified by participants on the network.</li>
<li>Once a block is added, changing it would require changing every block after it — which makes the history very hard to alter.</li>
<li>No single party needs to be trusted, because thousands of copies of the ledger exist at once.</li>
</ul>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Coins vs Tokens: What\'s the Difference?</h2>
<p class="mb-3">"Coins" like Bitcoin or Ether usually run on their own independent blockchain. "Tokens," on the other hand, are typically built on top of an existing blockchain (Ethereum is a common example) and can represent anything from a share in a project to access rights within an application.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Why Do Cryptocurrency Prices Change So Much?</h2>
<p class="mb-3">Crypto markets trade 24 hours a day and are influenced by supply and demand, investor sentiment, regulatory news, macroeconomic conditions and the relative size of each market. Because many cryptocurrencies have smaller total markets than traditional assets like stocks, prices can move sharply in short periods of time.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Key Terms Every Beginner Should Know</h2>
<ul class="list-disc pl-5 space-y-1.5 mb-3">
<li><strong class="text-slate-200">Wallet</strong> — software or hardware used to store the keys that control your crypto.</li>
<li><strong class="text-slate-200">Exchange</strong> — a platform where cryptocurrencies can be bought, sold or traded.</li>
<li><strong class="text-slate-200">Private key</strong> — a secret code that proves ownership of your crypto; anyone with it can control the funds.</li>
<li><strong class="text-slate-200">Market capitalization</strong> — the price of a coin multiplied by its circulating supply.</li>
<li><strong class="text-slate-200">Volatility</strong> — how sharply and quickly a price moves up or down.</li>
</ul>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Getting Started Responsibly</h2>
<p class="mb-3">If you\'re exploring cryptocurrency for the first time, it helps to start by learning rather than acting — understand how a project works, how to secure a wallet, and how volatile the market can be, before deciding whether it fits your own situation.</p>',
                ],
            ],

            [
                'article_category_id' => $market?->id,
                'title'       => 'How to Read Crypto Market Price Movements',
                'slug'        => 'how-to-read-crypto-market-price-movements',
                'excerpt'     => 'Understand what 1h, 24h and 7d percentage changes actually mean, and how metrics like volume and market cap fit into the full picture.',
                'meta_title'  => 'How to Read Crypto Market Price Movements',
                'meta_description' => 'A practical explainer on reading crypto price changes, trading volume, market cap and charts — without relying on a single number.',
                'related_coin_slugs' => ['bitcoin'],
                'published_days_ago' => 6,
                'sections' => [
                    '<h2 class="text-lg font-bold text-white mt-0 mb-3">Why Price Percentages Matter</h2>
<p class="mb-3">Most crypto trackers, including CryptoInfo, show price changes over multiple timeframes rather than a single number. Looking at more than one timeframe helps separate short-term noise from a longer trend.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">1h, 24h and 7d: What Do These Numbers Mean?</h2>
<p class="mb-3">Each percentage compares the current price to the price at the start of that specific window. A coin can be up over 24 hours while still being down over 7 days — both numbers are accurate, they just describe different periods.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Volume: The Number Behind the Price</h2>
<p class="mb-3">Trading volume measures how much of an asset changed hands in a given period. A price move backed by high volume generally reflects broader participation, while a move on unusually low volume can be more fragile and easier to reverse.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Market Cap vs Price: Why a $1 Coin Isn\'t "Cheaper" Than a $60,000 One</h2>
<p class="mb-3">The unit price of a coin depends on how many units exist — it says nothing about the size of the project on its own. Market capitalization (price × circulating supply) is a more useful way to compare the overall size of two different cryptocurrencies.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Reading a Price Chart</h2>
<p class="mb-3">Area charts give a quick visual sense of direction, while candlestick charts show the open, high, low and close for each time period, which can reveal more detail about how a price moved within a session.</p>
<ul class="list-disc pl-5 space-y-1.5 mb-3">
<li>Shorter timeframes (1H, 4H) show short-term fluctuation.</li>
<li>Longer timeframes (1M, 1Y) smooth out day-to-day noise.</li>
<li>No single timeframe is "correct" — they answer different questions.</li>
</ul>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Putting It Together</h2>
<p class="mb-3">Price, volume, market cap and multiple timeframes each tell part of the story. Relying on any single metric in isolation makes it easy to misread what is actually happening in a market.</p>',
                ],
            ],

            [
                'article_category_id' => $beginner?->id,
                'title'       => "Bitcoin vs Ethereum vs Altcoins: What's the Difference?",
                'slug'        => 'bitcoin-vs-ethereum-vs-altcoins-differences',
                'excerpt'     => "Bitcoin, Ethereum and thousands of altcoins are often lumped together as 'crypto' — here's how they actually differ in purpose and design.",
                'meta_title'  => 'Bitcoin vs Ethereum vs Altcoins Explained',
                'meta_description' => 'A clear comparison of Bitcoin, Ethereum and altcoins: what each one is designed to do, and how their technology differs.',
                'related_coin_slugs' => ['bitcoin', 'ethereum'],
                'published_days_ago' => 9,
                'sections' => [
                    '<h2 class="text-lg font-bold text-white mt-0 mb-3">Bitcoin: Digital Gold</h2>
<p class="mb-3">Bitcoin was designed primarily as a decentralized, censorship-resistant store of value and medium of exchange, with a fixed maximum supply of 21 million coins. Its core focus has stayed narrow: a simple, secure way to transfer and hold value without relying on a bank or government.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Ethereum: A Platform, Not Just a Coin</h2>
<p class="mb-3">Ethereum introduced "smart contracts" — small programs that run on the blockchain itself. This turned Ethereum into a platform for building applications (often called dApps), from decentralized exchanges to lending protocols, with Ether (ETH) used to pay for computation on the network.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">What Are Altcoins?</h2>
<p class="mb-3">"Altcoin" simply means any cryptocurrency that isn\'t Bitcoin. Some altcoins compete directly with Ethereum as smart-contract platforms, others focus on privacy, fast payments, gaming, or specific industries — the category is extremely broad.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Comparing Them Side by Side</h2>
<ul class="list-disc pl-5 space-y-1.5 mb-3">
<li><strong class="text-slate-200">Primary purpose:</strong> Bitcoin — store of value; Ethereum — programmable platform; Altcoins — varies widely.</li>
<li><strong class="text-slate-200">Supply model:</strong> Bitcoin has a hard cap of 21M; other networks set their own rules.</li>
<li><strong class="text-slate-200">Smart contracts:</strong> Native to Ethereum and many altcoins; not part of Bitcoin\'s original design.</li>
<li><strong class="text-slate-200">Track record:</strong> Bitcoin and Ethereum have the longest continuous operating history among major networks.</li>
</ul>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Which One Should You Learn About First?</h2>
<p class="mb-3">There\'s no universal answer — it depends on what you\'re trying to understand. Many beginners start with Bitcoin and Ethereum simply because they have the longest history and the most educational material available, then explore altcoins once the fundamentals feel familiar.</p>',
                ],
            ],

            [
                'article_category_id' => $security?->id,
                'title'       => 'How to Secure Your Crypto Wallet',
                'slug'        => 'how-to-secure-your-crypto-wallet',
                'excerpt'     => 'Wallet security mistakes are one of the most common reasons people lose access to their crypto. Here are the fundamentals of keeping funds safe.',
                'meta_title'  => 'How to Secure Your Crypto Wallet',
                'meta_description' => 'Practical, beginner-friendly wallet security basics: seed phrases, hot vs cold storage, common scams, and a simple security checklist.',
                'related_coin_slugs' => [],
                'published_days_ago' => 12,
                'sections' => [
                    '<h2 class="text-lg font-bold text-white mt-0 mb-3">Why Wallet Security Matters More in Crypto</h2>
<p class="mb-3">With traditional banking, a lost card or a fraudulent charge can usually be reversed by calling your bank. Most cryptocurrency transactions are irreversible, and if the keys controlling a wallet are lost or stolen, there is typically no customer support line that can undo it.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Hot Wallets vs Cold Wallets</h2>
<p class="mb-3">A "hot wallet" stays connected to the internet (an app or browser extension), which is convenient but exposes it to more attack surface. A "cold wallet" — like a dedicated hardware device — keeps private keys offline, which is generally considered safer for larger or long-term holdings.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Protecting Your Seed Phrase</h2>
<p class="mb-3">A seed phrase (usually 12–24 words) can restore full access to a wallet. Treat it like the master key to a safe.</p>
<ul class="list-disc pl-5 space-y-1.5 mb-3">
<li>Never type it into a website, chatbot, or "support" form.</li>
<li>Never store it as a plain photo or note in cloud storage or email.</li>
<li>Write it down offline and store copies in secure physical locations.</li>
<li>No legitimate company or support agent will ever ask for it.</li>
</ul>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Common Scams to Watch For</h2>
<ul class="list-disc pl-5 space-y-1.5 mb-3">
<li><strong class="text-slate-200">Phishing sites</strong> that copy real exchange or wallet login pages.</li>
<li><strong class="text-slate-200">Fake support accounts</strong> on social media offering to "help" with a wallet issue.</li>
<li><strong class="text-slate-200">Giveaway scams</strong> promising to double any crypto sent to an address.</li>
<li><strong class="text-slate-200">Malicious browser extensions</strong> that quietly intercept wallet activity.</li>
</ul>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">A Simple Security Checklist</h2>
<ul class="list-disc pl-5 space-y-1.5 mb-3">
<li>Enable two-factor authentication on every exchange account.</li>
<li>Consider a hardware wallet once holdings grow beyond "pocket money."</li>
<li>Always double-check URLs before entering credentials or a seed phrase.</li>
<li>Keep large, long-term holdings separate from wallets used for everyday activity.</li>
</ul>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Final Thoughts</h2>
<p class="mb-3">Most crypto losses come from security mistakes rather than market movements. A small amount of upfront caution — a hardware wallet, a securely stored seed phrase, and healthy skepticism toward unsolicited "help" — goes a long way.</p>',
                ],
            ],

            [
                'article_category_id' => $beginner?->id,
                'title'       => 'Common Mistakes Crypto Beginners Make',
                'slug'        => 'common-mistakes-crypto-beginners-make',
                'excerpt'     => 'From skipping security basics to reacting emotionally to price swings, these are some of the most frequent mistakes new crypto users make.',
                'meta_title'  => 'Common Mistakes Crypto Beginners Make',
                'meta_description' => 'The most common mistakes crypto beginners make — and simple habits that help avoid them.',
                'related_coin_slugs' => [],
                'published_days_ago' => 15,
                'sections' => [
                    '<h2 class="text-lg font-bold text-white mt-0 mb-3">Mistake #1: Not Understanding What You\'re Buying</h2>
<p class="mb-3">It\'s common for beginners to buy a coin because it\'s "trending" without understanding what the project actually does, who runs it, or why it might have value. A few minutes reading a project\'s own documentation can prevent a lot of confusion later.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Mistake #2: Ignoring Wallet Security</h2>
<p class="mb-3">Reusing passwords, skipping two-factor authentication, or storing a seed phrase in an unsecured note are among the most preventable causes of losing access to funds. Basic wallet hygiene matters as much as, if not more than, picking the "right" coin.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Mistake #3: Reacting Emotionally to Price Swings</h2>
<p class="mb-3">Because crypto markets are volatile and trade around the clock, it\'s easy to make quick decisions driven by fear or excitement rather than a clear plan. Sharp short-term swings are a normal feature of these markets, not necessarily a signal on their own.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Mistake #4: Keeping Everything on One Exchange</h2>
<p class="mb-3">Leaving all holdings on a single exchange concentrates risk in one place. Many users choose to split funds between an exchange (for active use) and a personal wallet (for longer-term storage).</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Mistake #5: Skipping Research Before Following Trends</h2>
<p class="mb-3">A coin appearing on a "trending" or "top gainers" list reflects recent market activity, not a guarantee of future performance. Treating any single list as a shortcut for research is a common and avoidable mistake.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">How to Avoid These Mistakes</h2>
<p class="mb-3">Learning the basics first, securing wallets properly, spreading out risk, and treating market data as one input among many are simple habits that address most of the mistakes above.</p>',
                ],
            ],

            [
                'article_category_id' => $beginner?->id,
                'title'       => 'Understanding Stablecoins: What They Are and How They Work',
                'slug'        => 'understanding-stablecoins',
                'excerpt'     => "Stablecoins try to avoid crypto's typical volatility by tracking the value of an external asset like the US dollar. Here's how the main types work.",
                'meta_title'  => 'Understanding Stablecoins: A Beginner\'s Explainer',
                'meta_description' => 'What stablecoins are, the main types (fiat-backed, crypto-backed, algorithmic), what they\'re used for, and the risks involved.',
                'related_coin_slugs' => [],
                'published_days_ago' => 18,
                'sections' => [
                    '<h2 class="text-lg font-bold text-white mt-0 mb-3">What Is a Stablecoin?</h2>
<p class="mb-3">A stablecoin is a type of cryptocurrency designed to maintain a stable value, usually by tracking a fiat currency like the US dollar. Their goal is to combine the transferability of crypto with the price stability of traditional money.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Fiat-Collateralized Stablecoins</h2>
<p class="mb-3">These are backed by reserves of traditional currency or equivalent assets held by the issuer, ideally at a 1:1 ratio. Their stability depends heavily on how those reserves are managed and how transparently they are reported.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Crypto-Collateralized Stablecoins</h2>
<p class="mb-3">Instead of holding fiat currency, these stablecoins are backed by other cryptocurrencies, usually over-collateralized to absorb price swings in the underlying assets. This adds a layer of complexity but removes reliance on a single traditional bank account.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Algorithmic Stablecoins</h2>
<p class="mb-3">Some stablecoins try to maintain their value using automated supply adjustments and market incentives rather than holding collateral directly. This design has historically proven more fragile in stressed market conditions than collateral-backed approaches.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">What Stablecoins Are Commonly Used For</h2>
<ul class="list-disc pl-5 space-y-1.5 mb-3">
<li>As a trading pair on exchanges instead of constantly converting to fiat currency.</li>
<li>Moving value between exchanges or wallets quickly.</li>
<li>Temporarily stepping out of more volatile assets without leaving the crypto ecosystem entirely.</li>
</ul>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Are Stablecoins Risk-Free?</h2>
<p class="mb-3">No. "Stable" refers to the intended price behavior, not the absence of risk. Reserve mismanagement, counterparty issues, or a loss of confidence in an issuer can all cause a stablecoin to lose its peg, so it\'s worth understanding how a specific stablecoin is backed before relying on it.</p>',
                ],
            ],

            [
                'article_category_id' => $market?->id,
                'title'       => 'How to Follow Crypto Market Trends',
                'slug'        => 'how-to-follow-crypto-market-trends',
                'excerpt'     => "'Trending' doesn't always mean 'going up.' Here's how trend indicators like volume, gainers/losers lists and sentiment indexes fit together.",
                'meta_title'  => 'How to Follow Crypto Market Trends',
                'meta_description' => 'How to interpret trending lists, gainers/losers, trading volume and sentiment indexes like the Fear & Greed Index together.',
                'related_coin_slugs' => [],
                'published_days_ago' => 21,
                'sections' => [
                    '<h2 class="text-lg font-bold text-white mt-0 mb-3">What Does "Trending" Actually Mean?</h2>
<p class="mb-3">On most platforms, including CryptoInfo, a "trending" list is typically ranked by trading volume or recent search interest — not necessarily by which direction the price is moving. A coin can trend while its price is falling just as easily as while it\'s rising.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Gainers and Losers Lists</h2>
<p class="mb-3">Top gainers and losers lists rank assets by percentage price change over a set period, usually 24 hours. They\'re useful for spotting unusual activity quickly, but a large percentage move on a very small or thinly traded asset can be less meaningful than a smaller move on a larger, more liquid one.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Volume as a Trend Signal</h2>
<p class="mb-3">Sustained increases in trading volume often accompany genuine shifts in market interest, while price moves on unusually thin volume can reverse quickly. Volume adds useful context to a price chart on its own.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Market Sentiment: The Fear & Greed Index</h2>
<p class="mb-3">Sentiment indexes like the Fear & Greed Index combine several inputs — volatility, momentum, social activity and more — into a single score meant to describe the market\'s overall mood, from "Extreme Fear" to "Extreme Greed." It reflects sentiment, not a prediction.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Combining Signals Instead of Chasing One Metric</h2>
<p class="mb-3">Trending lists, gainers/losers, volume and sentiment each describe a different angle of the same market. Looking at more than one at a time gives a fuller picture than reacting to any single ranking.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">A Balanced Approach</h2>
<p class="mb-3">Market indicators are tools for understanding what is happening, not instructions for what to do next. Treating them as one part of broader research is a more balanced way to follow a fast-moving market.</p>',
                ],
            ],

            [
                'article_category_id' => $market?->id,
                'title'       => 'What Is Market Capitalization in Crypto?',
                'slug'        => 'what-is-market-capitalization-in-crypto',
                'excerpt'     => "Market cap is one of the most quoted numbers in crypto — and one of the most misunderstood. Here's what it measures and where it falls short.",
                'meta_title'  => 'What Is Market Capitalization in Crypto?',
                'meta_description' => 'What crypto market capitalization measures, how it differs from circulating/total/max supply, and its limitations as a metric.',
                'related_coin_slugs' => ['bitcoin', 'ethereum'],
                'published_days_ago' => 24,
                'sections' => [
                    '<h2 class="text-lg font-bold text-white mt-0 mb-3">The Market Cap Formula</h2>
<p class="mb-3">Market capitalization is calculated as current price multiplied by circulating supply. It\'s meant to represent the total value of all coins currently in circulation, and it\'s the main number used to rank cryptocurrencies by size.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Why Market Cap Isn\'t the Same as "Money Invested"</h2>
<p class="mb-3">Market cap is a calculated figure, not a record of how much money has actually flowed into an asset. Because it\'s based on the last traded price applied to the entire supply, it can move sharply even when only a small fraction of coins actually changed hands.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Circulating Supply vs Total Supply vs Max Supply</h2>
<p class="mb-3"><strong class="text-slate-200">Circulating supply</strong> is the number of coins currently available and tradable. <strong class="text-slate-200">Total supply</strong> includes coins that exist but may be locked or reserved. <strong class="text-slate-200">Max supply</strong> is the absolute cap that will ever exist, if one is defined at all.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Market Cap Rankings: What They Do (and Don\'t) Tell You</h2>
<p class="mb-3">A higher market cap ranking generally reflects a larger, often more established asset — but it says nothing directly about the quality of the underlying technology, team, or long-term prospects of a project.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Fully Diluted Valuation: A Related Metric</h2>
<p class="mb-3">Fully diluted valuation (FDV) estimates market cap as if the maximum possible supply were already in circulation. Comparing FDV to current market cap can highlight how much future supply is still expected to enter the market.</p>',

                    '<h2 class="text-lg font-bold text-white mt-6 mb-3">Using Market Cap Wisely</h2>
<p class="mb-3">Market cap is a useful starting point for comparing the relative size of different cryptocurrencies, but it works best alongside other metrics like volume, supply schedule and project fundamentals rather than as a standalone measure.</p>',
                ],
            ],
        ];

        foreach ($articles as $data) {
            $daysAgo = $data['published_days_ago'];
            unset($data['published_days_ago']);

            Article::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, [
                    'author_name'  => 'CryptoInfo Team',
                    'status'       => 'published',
                    'published_at' => now()->subDays($daysAgo),
                ])
            );
        }
    }
}
