# CLAUDE.md — Mémoire de projet CryptoInfo

> Généré par analyse statique du dépôt le 2026-07-21. Ce fichier est factuel : tout point non vérifiable par la lecture du code est marqué **[À VÉRIFIER]**.
> Ne pas modifier les fichiers applicatifs à partir de ce document — c'est une carte, pas une source de vérité (le code l'est).
>
> **Règle de tenue à jour** : à la fin de chaque module ajouté/modifié significatif, l'utilisateur demandera explicitement *« Mets à jour CLAUDE.md avec ce qu'on vient d'ajouter »*. Éditer alors uniquement les sections concernées, sans réécrire tout le fichier.

---

## 0. Résumé en une phrase

Application Laravel mono-repo (pas de séparation front/back) : site public de données de marché crypto (prix temps réel, comparateur, blog, news) + un back-office Filament pour gérer un module "News" éditorial. Base de données SQLite par défaut en dev.

---

## 1. STACK

### Backend (composer.json + composer.lock)
| Package | Contrainte composer.json | Version installée (lock) |
|---|---|---|
| PHP | `^8.3` | **8.4.0** (CLI locale — [À VÉRIFIER] version du serveur de prod) |
| laravel/framework | `^13.8` | **v13.17.0** |
| filament/filament | `^5.6` | **v5.6.7** |
| laravel/breeze | `^2.4` | **v2.4.2** (installé mais **non câblé** — voir §10) |
| laravel/reverb | `^1.10` | **v1.10.2** (serveur WebSocket, broadcasting) |
| laravel/tinker | `^3.0` | — |
| openai-php/laravel | `^0.20.0` | **v0.20.0** |
| predis/predis | `^3.5` | — (client Redis, [À VÉRIFIER] si Redis réellement utilisé en prod — `.env.example` a `CACHE_STORE=database`, `QUEUE_CONNECTION=database`) |
| spatie/laravel-sitemap | `^8.2` | **8.2.0** |
| laravel/pint | `^1.27` | dev — formatteur de code PHP (style Laravel/PSR-12) |
| laravel/pail | `^1.2.5` | dev — tail des logs |
| phpunit/phpunit | `^12.5.12` | dev — **PHPUnit classique**, pas Pest (voir `tests/`, classes `extends TestCase`, méthodes `test_snake_case`) |

### Frontend (package.json + package-lock.json)
| Package | Contrainte | Version installée |
|---|---|---|
| alpinejs | `^3.4.2` | **3.15.12** |
| vite | `^8.0.0` | **8.1.0** |
| laravel-vite-plugin | `^3.1` | **3.1.0** |
| tailwindcss | `^4.3.1` | **4.3.1** (Tailwind v4, config via `@tailwindcss/vite`, pas de `tailwind.config.js` classique en pipeline PostCSS séparé — voir `vite.config.js` / `postcss.config.js`) |
| @tailwindcss/vite | `^4.3.1` | **4.3.1** |
| laravel-echo | `^2.3.7` | **2.3.7** |
| pusher-js | `^8.5.0` | **8.5.0** (protocole Pusher utilisé comme client pour **Reverb**, pas Pusher SaaS) |
| concurrently | `^9.0.1` | — (orchestre `serve` + `queue`/`scheduler` + `vite` dans les scripts composer `dev`/`start`) |

Node local : **v22.21.0**. [À VÉRIFIER] version Node exigée en CI/prod.

### Base de données / cache / queue (par défaut, `.env.example`)
- `DB_CONNECTION=sqlite`
- `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database`
- `BROADCAST_CONNECTION=log` par défaut dans `.env.example` (⚠️ pour que le WebSocket Reverb fonctionne réellement il faut `BROADCAST_CONNECTION=reverb` + `REVERB_*` — voir `config/broadcasting.php`)

### Fichiers de build / build tooling divers à la racine
- `cryptoinfo.zip` (~68 Mo) et `public/public.zip` : archives, probablement des sauvegardes/exports — **ne pas les lire pour comprendre l'app**, ce ne sont pas des sources. [À VÉRIFIER] leur utilité, candidates à `.gitignore`.

---

## 2. ARCHITECTURE — arborescence commentée

```
app/
  Console/Commands/
    FetchCryptoData.php        # sync CoinGecko -> table cryptocurrencies (cron 10 min)
    FetchNews.php               # sync RSS CryptoPanic -> table news (cron 30 min)
  Events/
    PriceUpdated.php            # event broadcast (ShouldBroadcast) sur le channel "crypto-prices"
  Filament/
    Resources/NewsPosts/        # unique ressource admin actuelle (voir §9)
      NewsPostResource.php
      Pages/{List,Create,Edit}NewsPost.php
      Schemas/NewsPostForm.php  # formulaire (pattern Filament v4/v5 "Schema")
      Tables/NewsPostsTable.php # colonnes + actions de la liste
  Http/
    Controllers/
      Api/CoinController.php    # endpoints JSON /api/*
      ArticleController.php     # blog (model Article)
      CompareController.php     # comparateur 2 cryptos
      CryptoController.php      # page marché principale (index/show)
      LocaleController.php      # switch de langue (session)
      MarketController.php      # gainers/losers/trending/F&G/dominance/cap
      NewsController.php        # module "News" éditorial (model NewsPost)
      SitemapController.php     # génère/sert sitemap.xml
      StaticPageController.php  # about/privacy/terms
    Middleware/
      SecurityHeaders.php       # headers de sécurité globaux (append au groupe web)
      SetLocale.php             # lit la locale en session, App::setLocale()
  Models/                       # voir §3
  Providers/
    AppServiceProvider.php      # Builder::defaultStringLength(191) seulement
    Filament/AdminPanelProvider.php  # config du panel /admin
  Services/
    AiSummaryService.php        # wrapper OpenAI (résumés news + "pourquoi le prix bouge")
    CryptoApiService.php        # client HTTP CoinGecko (markets + detail coin)
    CryptoBroadcastService.php  # construit le payload et dispatch PriceUpdated
    GlobalMarketService.php     # stats globales CoinGecko /global + Fear&Greed (alternative.me)
    NewsApiService.php          # parseur RSS CryptoPanic
    SeoService.php              # objet de valeur SEO par page (voir §7)

routes/
  web.php      # toutes les pages publiques + /lang/{locale} + /sitemap.xml + /robots.txt
  api.php      # /api/coins, /api/gainers, /api/losers, /api/trending (throttle 60/min)
  console.php  # Schedule:: (cron) + commande artisan:inspire

resources/
  views/
    layouts/app.blade.php       # LE layout principal (head SEO, header, footer, theme)
    components/                 # Blade components — mélange Breeze (inutilisés, voir §10)
                                 # et composants actifs (percent-badge.blade.php)
    partials/
      global-ticker.blade.php       # barre globale tout en haut (stats live cachées 5 min)
      language-switcher.blade.php   # dropdown Alpine de langue (desktop)
      hero.blade.php                # hero page d'accueil (H1 SEO)
      _coin-table.blade.php         # <table> réutilisée par gainers/losers/trending
      content-disclaimer.blade.php  # bandeau "not financial advice"
    crypto/{index,show}.blade.php
    market/{gainers,losers,trending,fear-greed,bitcoin-dominance,global-cap,compare,compare-chooser}.blade.php
    blog/{index,show}.blade.php     # model Article
    news/{index,show}.blade.php     # model NewsPost (back-office Filament)
    pages/{about,privacy,terms}.blade.php
    errors/{404,500}.blade.php
    vendor/pagination/*              # vues de pagination publiées (thèmes Bootstrap/Tailwind)
    welcome.blade.php                # page par défaut Laravel, [À VÉRIFIER] si encore accessible
                                      # (aucune route ne pointe dessus dans web.php — probablement mort code)
  js/app.js       # Alpine + Laravel Echo/Reverb + client WebSocket Binance natif (voir §5)
  css/app.css     # Tailwind v4 (import via @tailwindcss/vite)

database/
  migrations/     # voir §3 — style "une migration par changement", commentaires explicatifs fréquents
  seeders/        # ArticleCategorySeeder, ArticleSeeder, NewsPostSeeder, DatabaseSeeder
  factories/      # UserFactory.php (seul factory présent)

lang/
  en.json fr.json ar.json es.json de.json pt.json   # fichiers JSON plats (voir §6)

config/
  services.php    # bloc "coingecko" (url + clé API optionnelle)
  broadcasting.php, reverb.php  # config Reverb/WebSocket
  (pas de config/openai.php custom — package openai-php/laravel publie le sien, [À VÉRIFIER] chemin exact)

tests/
  Feature/  # BlogPageTest, NewsPageTest, NewsPostAdminTest, SeoMetadataTest, SeoServiceTest,
            # CacheSerializationTest, RemovedUserNewsletterRoutesTest (test de régression sur des
            # routes supprimées — voir migration 2026_07_01_010000)
  Unit/ExampleTest.php
  TestCase.php
```

---

## 3. MODÈLES DE DONNÉES

### `Cryptocurrency` — table `cryptocurrencies`
Fichier : `app/Models/Cryptocurrency.php` · Migration de base : `2026_06_24_111000_create_cryptocurrencies_table.php` (+ patches ultérieurs)

| Colonne | Type (migration) | Notes |
|---|---|---|
| id | bigint PK | |
| name, symbol(20), slug | string, `slug` unique | |
| image_url | string nullable | |
| current_price | decimal(30,10) | precision large pour micro-caps |
| market_cap | decimal(30,2) | |
| market_cap_rank | unsignedInteger, indexé | tri principal des listings |
| fully_diluted_valuation | decimal(30,2) | |
| total_volume | decimal(30,2) | |
| high_24h, low_24h | decimal(30,10) | |
| price_change_percentage_{1h,24h,7d}_in_currency | decimal(10,4), indexés (24h et 7d) | |
| circulating_supply, total_supply, max_supply | decimal(30,2) | |
| ath, atl | decimal(30,10) | |
| ath_change_percentage, atl_change_percentage | decimal(20,4) — élargi depuis decimal(10,4) car certains micro-caps dépassent 100 000 000 % | migration `2026_06_24_123632_fix_percentage_columns...` |
| ath_date, atl_date | timestamp nullable | |
| sparkline_7d | json nullable | ajouté par `2026_06_24_152000_add_sparkline...`, cast Eloquent `array` |
| description | text nullable | |
| views_count | unsignedBigInteger default 0, indexé | ajouté par `2026_06_24_143737_add_views_count...` |
| ai_summary | string nullable | ajouté dans la même migration que `views_count` |
| timestamps | | |

Index additionnels : `idx_change_24h`, `idx_change_7d`, `idx_rank_id` (composite `market_cap_rank,id`), fulltext `ft_name_symbol` sur `name,symbol` (**skip sur sqlite** — `DB::getDriverName() !== 'sqlite'`).

Pas de relation Eloquent déclarée (aucune FK entrante/sortante). Le modèle porte des helpers d'affichage : `formattedPrice()`, `percentColor()`, `percentArrow()`. Beaucoup de code (controllers) contourne l'ORM et fait des requêtes `DB::table('cryptocurrencies')` brutes puis `->forceFill()` dans une instance `Cryptocurrency` — **pattern récurrent pour la perf** (voir §10).

### `Article` — table `articles`
Fichier : `app/Models/Article.php` · Migration : `2026_07_06_120100_create_articles_table.php`

| Colonne | Type | Notes |
|---|---|---|
| id | PK | |
| article_category_id | FK nullable → `article_categories.id`, `nullOnDelete` | |
| title, slug (unique) | string | |
| excerpt | string(300) nullable | |
| sections | json (non nullable) | contenu de l'article structuré en sections |
| cover_image_url | string nullable | |
| meta_title, meta_description | string nullable | override SEO |
| related_coin_slugs | json nullable | liens vers `cryptocurrencies.slug` (pas de FK réelle) |
| author_name | string, default `'CryptoInfo Team'` | |
| status | enum `draft`/`published`, default `draft` | |
| published_at | timestamp nullable | |
| views_count | unsignedInteger default 0 | |
| timestamps | index composite `(status, published_at)` | |

Relations : `category(): BelongsTo → ArticleCategory`. Route key = `slug`. Scope `published()` (status + published_at ≤ now). C'est le modèle du **blog** (`/blog`), distinct du module News.

### `ArticleCategory` — table `article_categories`
Fichier : `app/Models/ArticleCategory.php` · Migration : `2026_07_06_120000_create_article_categories_table.php`
Colonnes : `id, name, slug (unique), description nullable, timestamps`. Relation `articles(): HasMany`. Route key = `slug`.

### `News` — table `news`
Fichier : `app/Models/News.php` · Migration : `2026_06_24_143737_create_news_table.php` (+ `views_count` ajouté `2026_07_01_000000`)

| Colonne | Type | Notes |
|---|---|---|
| id | PK | |
| title, slug (unique) | string | |
| summary, ai_summary | text nullable | `ai_summary` généré par `AiSummaryService::summarizeNews()` |
| url | string | lien externe vers l'article source |
| source | string nullable | ex. `"CryptoPanic"` |
| image_url | string nullable | |
| coin_slugs | json nullable | cast `array` |
| sentiment | string default `neutral` | positive/neutral/negative |
| published_at | timestamp nullable, indexé | |
| views_count | unsignedBigInteger default 0 | |

⚠️ **Ce modèle n'a pas de route publique dédiée.** Il est alimenté par `php artisan app:fetch-news` (RSS CryptoPanic) mais n'est actuellement affiché **nulle part** dans `routes/web.php` — `SeoService::forNews()` pointe volontairement le canonical vers la home avec un commentaire explicite à ce sujet. **Ne pas confondre avec `NewsPost`** (voir ci-dessous), qui lui a les routes `/news` et `/news/{slug}`. [À VÉRIFIER] : `News` semble être un module en friche/legacy — à clarifier avant d'y ajouter des fonctionnalités.

### `NewsPost` — table `news_posts`
Fichier : `app/Models/NewsPost.php` · Migration : `2026_07_09_000001_create_news_posts_table.php`

| Colonne | Type | Notes |
|---|---|---|
| id | PK | |
| title, slug (unique) | string | slug auto-généré depuis `title` si vide (hook `booted()` → `static::saving`), avec dédoublonnage `-2`, `-3`... |
| excerpt | string(300) nullable | |
| content | **longText** (non nullable) | contenu riche (Filament `RichEditor`) |
| featured_image | string nullable | chemin sur disque `public` (`storage/app/public/news/...`) |
| status | enum `draft`/`published`, default `draft` | |
| published_at | timestamp nullable | |
| meta_title, meta_description | string nullable | |
| timestamps | index composite `(status, published_at)` | |

Accesseurs : `reading_time` (calcul mots/200), `featured_image_url` (`asset('storage/'.featured_image)`). Scope `published()`. Route key = `slug`. **C'est le modèle géré par le back-office Filament** (`NewsPostResource`) et servi publiquement sur `/news` et `/news/{slug}`.

### `User` — table `users`
Fichier : `app/Models/User.php` · Migration : `2026_07_09_000000_create_users_table.php`
Colonnes standard Laravel (`name, email unique, email_verified_at, password, remember_token, timestamps`). Implémente `FilamentUser` (`canAccessPanel()` retourne toujours `true` — **pas de restriction de rôle**, tout utilisateur authentifié accède à `/admin`). ⚠️ Historique : une première table `users` avait été **supprimée** par la migration `2026_07_01_010000_remove_user_newsletter_tables.php` (avec `watchlists`, `price_alerts`, `newsletter_subscribers`, `password_reset_tokens`) puis **recréée** par `2026_07_09_000000` uniquement pour l'auth Filament — le site public reste stateless/sans compte utilisateur. Table `password_reset_tokens` non recréée [À VÉRIFIER si nécessaire pour un flux "mot de passe oublié" admin].

### Diagramme relationnel (texte)
```
ArticleCategory 1---N Article        (FK article_category_id, nullOnDelete)
Cryptocurrency  (aucune FK)          — référencée par slug (non contraint) depuis Article.related_coin_slugs et News/NewsPost.coin_slugs
NewsPost        (aucune FK)          — table indépendante, gérée via Filament
News            (aucune FK)          — table indépendante, alimentée par cron RSS, pas de vue publique
User            (aucune FK)          — auth Filament uniquement
```

---

## 4. ROUTES — inventaire complet

### `routes/web.php` (groupe `web`, préfixe aucun)
| Méthode | URI | Nom | Controller@action |
|---|---|---|---|
| GET | `/lang/{locale}` (en\|fr\|ar\|es\|de\|pt) | `locale.switch` | `LocaleController@switch` |
| GET | `/` | `crypto.index` | `CryptoController@index` |
| GET | `/currencies/{slug}` | `crypto.show` | `CryptoController@show` |
| GET | `/crypto/{slug}-price` | `crypto.show.seo` | `CryptoController@show` (alias SEO du même contrôleur) |
| GET | `/compare` | `crypto.compare.chooser` | `CompareController@chooser` |
| GET | `/compare/{slugA}-vs-{slugB}` | `crypto.compare` | `CompareController@show` |
| GET | `/gainers` | `market.gainers` | `MarketController@gainers` |
| GET | `/losers` | `market.losers` | `MarketController@losers` |
| GET | `/trending` | `market.trending` | `MarketController@trending` |
| GET | `/best-performing-coins` | `market.best-performing` | `MarketController@gainers` (alias SEO) |
| GET | `/worst-performing-coins` | `market.worst-performing` | `MarketController@losers` (alias SEO) |
| GET | `/trending-cryptocurrencies` | `market.trending-seo` | `MarketController@trending` (alias SEO) |
| GET | `/fear-and-greed-index` | `market.fear-greed` | `MarketController@fearGreed` |
| GET | `/bitcoin-dominance` | `market.bitcoin-dominance` | `MarketController@bitcoinDominance` |
| GET | `/crypto-market-cap` | `market.global-cap` | `MarketController@globalMarketCap` |
| GET | `/global-crypto-volume` | `market.global-volume` | `MarketController@globalMarketCap` (alias SEO) |
| GET | `/blog` | `blog.index` | `ArticleController@index` |
| GET | `/blog/{article:slug}` | `blog.show` | `ArticleController@show` |
| GET | `/news` | `news.index` | `NewsController@index` |
| GET | `/news/{news:slug}` | `news.show` | `NewsController@show` |
| GET | `/about` | `pages.about` | `StaticPageController@about` |
| GET | `/privacy-policy` | `pages.privacy` | `StaticPageController@privacy` |
| GET | `/terms-of-service` | `pages.terms` | `StaticPageController@terms` |
| GET | `/sitemap.xml` | `sitemap` | `SitemapController@index` |
| GET | `/robots.txt` | *(anonyme)* | closure inline |

### `routes/api.php` (groupe `api`, préfixe automatique `/api`, throttle `60,1`)
| Méthode | URI réelle | Nom | Controller@action |
|---|---|---|---|
| GET | `/api/coins` | `api.coins.index` | `Api\CoinController@index` |
| GET | `/api/coins/{slug}` | `api.coins.show` | `Api\CoinController@show` |
| GET | `/api/gainers` | `api.gainers` | `Api\CoinController@gainers` |
| GET | `/api/losers` | `api.losers` | `Api\CoinController@losers` |
| GET | `/api/trending` | `api.trending` | `Api\CoinController@trending` |

### Routes hors `routes/*` (enregistrées par des Service Providers / packages)
- `/admin/*` → panel Filament (`AdminPanelProvider`, `path('admin')`), avec `/admin/login` géré par Filament et `/admin/news-posts` (index/create/{record}/edit) pour `NewsPostResource`.
- `/up` → health check Laravel (`bootstrap/app.php`, `health: '/up'`).

### ⚠️ Points d'attention pour les collisions futures
1. **`/gainers` (web) vs `/api/gainers` (api)** : pas de collision réelle (préfixes différents) mais **noms de route très proches** (`market.gainers` vs `api.gainers`) — bien vérifier le préfixe `route()` utilisé dans le code lors d'ajouts.
2. **Alias SEO multiples vers la même action** : `crypto.show`/`crypto.show.seo`, `market.gainers`/`market.best-performing`, `market.losers`/`market.worst-performing`, `market.trending`/`market.trending-seo`, `market.global-cap`/`market.global-volume`. C'est un pattern volontaire (mots-clés SEO) — **à reproduire à l'identique** si on ajoute une nouvelle page marché avec variante SEO, plutôt que créer une redirection.
3. **`{slug}` avec contrainte regex `[a-z0-9\-]+`** répétée sur plusieurs routes (`crypto.show`, `compare`, `blog.show`, `news.show`) — toute nouvelle route dynamique doit reprendre cette contrainte pour rester cohérente et éviter d'avaler des URIs plus spécifiques déclarées après elle.
4. **`/admin` est un préfixe réservé** par Filament — ne jamais déclarer de route web sous `/admin/...` dans `web.php`.
5. **Pas de route publique pour `News`** (le modèle RSS) — seulement pour `NewsPost` (back-office). Un futur ajout de route `/news/...` doit bien choisir le bon modèle.
6. Le nom `news.index`/`news.show` est déjà pris par `NewsPost` — ne pas réutiliser pour une éventuelle page publique du modèle `News`.

---

## 5. DONNÉES DE MARCHÉ — flux temps réel & synchronisation

### Vue d'ensemble : deux sources de prix, combinées côté client
1. **Binance WebSocket (source primaire, sub-seconde)** — `resources/js/app.js`, classe `BinanceWS`.
   - Connexion directe navigateur → `wss://stream.binance.com:9443/stream?streams=...` (aucun serveur intermédiaire).
   - `SLUG_TO_BINANCE` : table de correspondance codée en dur (slug CoinGecko → paire Binance, ~65 entrées, top-100 caps). `BINANCE_TO_SLUG` est la table inverse générée automatiquement.
   - Reconnexion avec backoff exponentiel (`1000 * 2^retry`, cap 30s, max 8 tentatives).
   - Écrit dans `Alpine.store('liveprices').prices[slug]` : `{price, open24h, change_24h, volume_24h, high_24h, low_24h, direction, ts}`.
   - Les vues Blade lisent ce store via `$store.liveprices.price(slug)`, `.change24h(slug)`, `.direction(slug)`, `.get(slug)`.
2. **Laravel Reverb / Echo (source secondaire, ~10 min)** — même fichier `app.js`, `globalThis.Echo` (driver `reverb`, clés `VITE_REVERB_*`), écoute le channel `crypto-prices`, event `.price.updated`.
   - Ne met à jour que les coins **absents** de `SLUG_TO_BINANCE` (stablecoins, tokens récents) — `if (!SLUG_TO_BINANCE[coin.slug])`.
   - Sert de filet de sécurité si le WebSocket Binance est bloqué (réseau d'entreprise, extension, etc.).

### Côté serveur : synchronisation planifiée (`routes/console.php`)
```php
Schedule::command('app:fetch-crypto-data')->everyTenMinutes()->withoutOverlapping()->runInBackground();
Schedule::command('app:fetch-news')->everyThirtyMinutes()->withoutOverlapping()->runInBackground();
```
Le scheduler Laravel doit tourner via `php artisan schedule:work` (voir script composer `start`) ou un cron système `* * * * * php artisan schedule:run`. [À VÉRIFIER] configuration cron en production.

- **`FetchCryptoData`** (`app:fetch-crypto-data`, `app/Console/Commands/FetchCryptoData.php`) :
  1. `CryptoApiService::fetchMarkets()` → `GET {COINGECKO_API_URL}/coins/markets` (top 250, `vs_currency=usd`, `price_change_percentage=1h,24h,7d`, `sparkline=true`), avec retry(2, 1000ms), timeout 15s, header `x-cg-demo-api-key` si `COINGECKO_API_KEY` défini.
  2. Upsert par lots de 50 dans `cryptocurrencies` (clé `slug`).
  3. Invalide les caches (`crypto_total_count`, `crypto_page_{1..5}_items`, `crypto_gainers`, `crypto_losers`, `crypto_trending`).
  4. Appelle `CryptoBroadcastService::broadcastPrices()` → dispatch `PriceUpdated` (event `ShouldBroadcast`, channel `crypto-prices`, event name `price.updated`) — c'est ce qui alimente le fallback Reverb côté client.
- **`FetchNews`** (`app:fetch-news`) : `NewsApiService::fetchLatest()` parse le flux RSS CryptoPanic (`https://cryptopanic.com/news/rss/`), déduplique par slug, enrichit optionnellement via `AiSummaryService::summarizeNews()` (OpenAI `gpt-4o-mini`, silencieux si `OPENAI_API_KEY` absent/placeholder), insère dans `News` (⚠️ pas `NewsPost`, voir §3).

### Autres données de marché
- **`GlobalMarketService`** (cache 10 min, clé `global_market_stats`) : agrège `GET https://api.coingecko.com/api/v3/global` (market cap total, dominance BTC/ETH, volume) + `GET https://api.alternative.me/fng/?limit=1` (Fear & Greed Index). Utilisé par `MarketController`, la ticker globale (`partials/global-ticker.blade.php`, cache 5 min séparé) et les pages `fear-greed`/`bitcoin-dominance`/`global-cap`.
- Tous les listings (`gainers`, `losers`, `trending`, page d'accueil, détail coin) passent par `Cache::remember(..., 300)` (5 min) sur des requêtes `DB::table('cryptocurrencies')` — **pas d'appel CoinGecko synchrone dans le rendu des pages**, uniquement lecture de la DB déjà synchronisée par le cron.
- `AiSummaryService::whyPriceMoved()` : génère une explication courte (OpenAI) affichée sur la page détail coin quand `|change_24h| >= 2`, cachée 30 min (`ai_why_{slug}`).

---

## 6. I18N — multilingue et RTL

### Mécanisme
- **6 locales supportées**, codées en dur en plusieurs endroits (`SetLocale::SUPPORTED`, `LocaleController::SUPPORTED`, contrainte regex de la route `/lang/{locale}`) : `en, fr, ar, es, de, pt`.
- **Pas de préfixe d'URL par langue** (`/fr/...` n'existe pas). La langue est stockée en **session** (`Session::put('locale', $locale)` dans `LocaleController@switch`) et lue à chaque requête par le middleware `SetLocale` (append au groupe `web` dans `bootstrap/app.php`), qui appelle `App::setLocale()`. Fallback : `en` si absent ou invalide.
- Changer de langue = `GET /lang/{locale}` puis redirection vers le referer (ou `crypto.index`).
- `config/app.php` : `locale = env('APP_LOCALE', 'en')`, `fallback_locale = env('APP_FALLBACK_LOCALE', 'en')`.

### Fichiers de traduction : `lang/*.json` (fichiers **plats**, pas de sous-dossiers par locale)
`lang/{en,fr,ar,es,de,pt}.json` — chacun **180 lignes**, mêmes clés (dot-notation à plat, ex. `"nav.market"`, `"footer.copyright"`). Utilisés via `__('nav.market')` dans les vues Blade.

**Pour ajouter une traduction : éditer les 6 fichiers `lang/*.json` en parallèle** (même clé, même position si possible pour faciliter le diff) — il n'y a pas de mécanisme de détection des clés manquantes, Laravel retombera silencieusement sur la clé brute si absente dans un fichier.

Namespaces de clés observés : `nav.*`, `lang.*`, `table.*`, `market.*`, `chart.*`, `compare.*`, `stats.*`, `btn.*`, `alert.*`, `newsletter.*` (⚠️ vestige — les tables `newsletter_subscribers` ont été supprimées, ces clés semblent orphelines, [À VÉRIFIER] avant de les réutiliser), `footer.*`, `trust.*`, `fng.*`, `btc.*`, `cap.*`, `pages.*`, `common.*`, `disclaimer.*`, `blog.*`, `news.*`.

### RTL (arabe)
- `resources/views/layouts/app.blade.php` calcule `$isRtl = app()->getLocale() === 'ar'` en tête de fichier et l'utilise pour :
  - `dir="rtl"` sur `<html>`.
  - Charger la police **Cairo** au lieu d'Inter (Google Fonts), forcée en `!important` sur `body, *`.
  - Classes conditionnelles **manuelles** ponctuelles (ex. `{{ $isRtl ? 'right-3' : 'left-3' }}`, `{{ $isRtl ? 'pr-10 pl-4' : 'pl-10 pr-4' }}`) sur les éléments qui ont une orientation (icônes de recherche, dropdowns).
  - Classe utilitaire `.rtl-flip` (transform `scaleX(-1)`) pour retourner des icônes directionnelles.
- **Il n'y a pas de solution générique automatique** (pas de variante Tailwind `rtl:`) — chaque nouvelle vue avec un élément asymétrique (padding, position, icône directionnelle) doit gérer `$isRtl` à la main, sur le modèle de `layouts/app.blade.php`. Penser à propager `$isRtl` (ou le recalculer via `app()->getLocale() === 'ar'`) dans toute nouvelle vue/partial qui en a besoin.
- Le language switcher (`partials/language-switcher.blade.php`) inverse aussi la position de son dropdown (`left-0` vs `right-0`) selon la locale.

---

## 7. SEO

### `App\Services\SeoService` (app/Services/SeoService.php)
Objet de valeur simple (propriétés publiques, pas d'interface), instancié soit via des **factory methods statiques** par type de page (`forHome()`, `forCoin($coin)`, `forNews($newsRssItem)`, `forMarket($type)`, `forBlogIndex()`, `forArticle($article)`, `forNewsIndex()`, `forNewsArticle($newsPost)`), soit construit à la main dans le contrôleur pour les pages statiques (`new SeoService(); $seo->title = ...`).

Propriétés : `title, description, canonical, image, og_type, robots, locale, alternateLanguages[], jsonld[], breadcrumbLabel, breadcrumbParent`.

Méthode d'instance `breadcrumbListJsonLd()` : génère un `BreadcrumbList` schema.org, retourne `null` sur la page d'accueil (recommandation Google).

### Où c'est rendu
Tout dans `resources/views/layouts/app.blade.php` (bloc `@isset($seo) ... @else ... @endisset` dans le `<head>`) :
- `<title>`, `<meta name="description">`, `<meta name="robots">` (défaut très permissif : `index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1`).
- `<link rel="canonical">`.
- **hreflang** : boucle sur `$seo->alternateLanguages` → `<link rel="alternate" hreflang="{lang}" href="{href}">`. ⚠️ Seule `SeoService::forHome()` a de vraies URLs par langue (`/lang/fr`, `/lang/ar`, etc.) ; toutes les autres factory methods ne mettent que `x-default` et `en` pointant vers **la même URL canonique** — il n'existe pas de contenu réellement traduit par URL, seulement du contenu dynamique traduit en session. **[À VÉRIFIER]** avant de considérer le hreflang comme complet sur les pages non-home.
- Open Graph complet (`og:type/locale/title/description/url/image(+alt/width/height)/site_name`) + Twitter Card (`summary_large_image`).
- JSON-LD : injecté brut (`json_encode(..., JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)`) depuis `$seo->jsonld`, puis un second `<script type="application/ld+json">` pour le `BreadcrumbList` si non-null. Types utilisés : `WebSite` (home, avec `SearchAction`), `FinancialProduct` (coin), `NewsArticle` (News RSS), `CollectionPage` (index blog/news), `Article` (article de blog et NewsPost).
- Image OG par défaut : `public/images/og-default.svg`.

### Sitemap — `SitemapController` (app/Http/Controllers/SitemapController.php)
- Généré **directement depuis les routes/DB** (pas de crawler — un ancien `SitemapGenerator::create()` de Spatie causait des timeouts `max_execution_time`, remplacé volontairement, voir commentaire dans le fichier).
- Écrit/caché sur disque : `storage/app/public/sitemap.xml`, régénéré seulement si absent ou vieux de plus de **12h** (`filemtime` check).
- Contenu : pages statiques (priorités 0.2 à 1.0) + toutes les `Cryptocurrency` (chunk 500, priorité 0.6, hourly) + tous les `Article` publiés (chunk 500, priorité 0.6, monthly) + tous les `NewsPost` publiés (chunk 500, priorité 0.6, weekly).
- Servi sur `GET /sitemap.xml` (`Content-Type: application/xml`).

### robots.txt
Route inline dans `routes/web.php` (pas de fichier statique) :
```
User-agent: *
Disallow: /lang/
Sitemap: {url('/sitemap.xml')}
```

---

## 8. LAYOUT

### Layout principal : `resources/views/layouts/app.blade.php`
Structure : `@php $isRtl ...` → `<head>` (meta/SEO/fonts/`@vite`) → `<body x-data="{ mobileMenuOpen: false }" x-init="$store.liveprices.init()">` → `@include('partials.global-ticker')` → `<header>` (logo, recherche, nav desktop, badge LIVE, toggle thème, language switcher, menu mobile) → `<main>@yield('content')</main>` → `<footer>` (5 colonnes : brand, market, content, company, trade/affiliés) → `@stack('scripts')`.
Les pages étendent ce layout via `@extends('layouts.app')` / `@section('content')` (Blade classique, pas de composants layout `<x-layout>`).

### Thème clair/sombre
- Anti-FOUC : script inline **synchrone** en tout premier dans `<head>` qui lit `localStorage.getItem('theme')` et pose la classe `dark`/`light` sur `<html>` **avant** le chargement de Tailwind.
- État géré par un **Alpine store** défini dans `resources/js/app.js` : `Alpine.store('theme', { dark, toggle(), init() })`, persiste dans `localStorage['theme']`, toggle les classes `dark`/`light` sur `document.documentElement`.
- Déclenché via `@click="$store.theme.toggle()"` (bouton header desktop + item menu mobile).
- ⚠️ Le `<body>` a la classe fixe `bg-slate-950 text-slate-100` et le `<html>` a `class="dark"` en dur dans le HTML — le thème clair semble **partiellement implémenté** (le store existe et fonctionne pour la classe CSS, mais la majorité des couleurs des vues sont écrites en dur avec la palette `slate-9xx` sans variante claire visible dans les fichiers consultés). **[À VÉRIFIER]** l'état réel du mode clair avant de s'appuyer dessus.

### Composants réutilisables
- **`resources/views/components/`** : mélange de deux origines.
  - Scaffolding **Laravel Breeze** non utilisé actuellement par le site (`application-logo`, `danger-button`, `dropdown`, `dropdown-link`, `input-error`, `input-label`, `modal`, `nav-link`, `primary-button`, `responsive-nav-link`, `secondary-button`, `text-input`) — aucune route d'auth n'existe dans `routes/web.php` (voir §10). À ne réactiver que si un flux d'auth public est ajouté.
  - Composant **actif** : `percent-badge.blade.php` (`<x-percent-badge :value="..."/>`) — affiche une variation % avec flèche ▲/▼ et couleur emerald/red, utilisé dans les tableaux de prix.
- **`resources/views/partials/`** : sections Blade réutilisables (pas des `@props` composants, juste des `@include`) :
  - `global-ticker.blade.php` — barre globale en tout haut de page (nb de cryptos, market cap total), cache 5 min.
  - `language-switcher.blade.php` — dropdown Alpine (`x-data="{ open: false }"`), liste les 6 langues avec drapeaux (`flagcdn.com`).
  - `hero.blade.php` — hero de la page d'accueil (porte le H1 SEO).
  - `_coin-table.blade.php` — table `<table>` réutilisée par gainers/losers/trending (préfixe `_` = partial "privé"/interne, seul fichier du dossier à suivre cette convention).
  - `content-disclaimer.blade.php` — bandeau "not financial advice".

---

## 9. FILAMENT

### Panel : `App\Providers\Filament\AdminPanelProvider`
- `id('admin')`, `path('admin')` → panel accessible sur `/admin`, avec `->login()` (auth Filament standard sur le modèle `User`).
- Couleur primaire : `Color::Amber`.
- Découverte automatique : `discoverResources(app_path('Filament/Resources'))`, `discoverPages(...)`, `discoverWidgets(...)`.
- Widgets déclarés : `AccountWidget`, `FilamentInfoWidget` (par défaut Filament, pas de widget custom).
- `User::canAccessPanel()` retourne toujours `true` — **aucune restriction par rôle**, tout compte dans `users` peut administrer.

### Ressource existante : `NewsPostResource` (`app/Filament/Resources/NewsPosts/`)
Seule ressource admin actuellement. Structure = **pattern Filament v4/v5 "resource dossier éclaté"** (au lieu d'un unique fichier monolithique) :
```
NewsPosts/
  NewsPostResource.php        # déclare model, icône, labels, pages, délègue form()/table()
  Schemas/NewsPostForm.php    # NewsPostForm::configure(Schema $schema): Schema
  Tables/NewsPostsTable.php   # NewsPostsTable::configure(Table $table): Table
  Pages/ListNewsPosts.php
  Pages/CreateNewsPost.php
  Pages/EditNewsPost.php
```
- Formulaire (`NewsPostForm`) : `Section` 2 colonnes avec `title` (auto-slug via `live(onBlur:true)` + `afterStateUpdated` sur `create` seulement), `slug` (unique ignoreRecord), `status` (select draft/published), `published_at` (DateTimePicker, sert de date d'affichage/planification), `excerpt` (Textarea 300 car max), `featured_image` (FileUpload, disk `public`, dossier `news`, max 4096 Ko), `content` (RichEditor) ; puis une `Section('SEO')` collapsible avec `meta_title`/`meta_description`.
- Table (`NewsPostsTable`) : colonnes image/titre/statut(badge)/date publication/date maj (masquée par défaut) ; tri par défaut `published_at desc` ; filtre `status` ; actions ligne custom `publish`/`unpublish` (toggle rapide du statut) + `EditAction`/`DeleteAction` ; `DeleteBulkAction` groupée.
- **Pattern à reproduire** pour toute nouvelle ressource Filament : dossier `Resources/<Nom>/` avec sous-dossiers `Pages/`, `Schemas/`, `Tables/`, classes de configuration statiques (`Xxx::configure($schema/$table)`), plutôt que tout mettre dans la classe `Resource`.

---

## 10. CONVENTIONS observées

- **Style de code** : `laravel/pint` en dev dependency → formatage Laravel/PSR-12 standard. Indentation 4 espaces, LF, UTF-8 (`.editorconfig`).
- **Tests** : PHPUnit classique (pas Pest) — classes `Tests\Feature\XxxTest extends Tests\TestCase`, méthodes `public function test_snake_case_description(): void`.
- **Commandes Artisan** : utilisent les **attributs PHP 8** `#[Signature('app:xxx')]` / `#[Description('...')]` plutôt que les propriétés `protected $signature`/`$description` (convention Laravel 12+/13).
- **Contrôleurs "lecture seule optimisée"** : pattern récurrent `Cache::remember($key, $ttl, fn() => DB::table(...)->...->get())` puis hydratation manuelle en modèle Eloquent via `(new Model())->forceFill((array) $row)` — évite le coût de l'ORM sur les listes lourdes (colonnes explicitement listées via `->select([...])` plutôt que `select *`). Constantes de classe `private const PER_PAGE`, `private const CACHE_TTL` en tête de contrôleur.
- **Services "value object" statiques** : `SeoService` n'est pas un singleton injecté mais un objet neuf à chaque appel (`new self()` dans des factory methods statiques, ou `new SeoService()` + assignation directe des propriétés publiques dans les contrôleurs de pages statiques). Pas d'interface/contrat.
- **Commentaires** : rares mais présents quand ils expliquent un **pourquoi** non trivial (ex. `SitemapController` explique pourquoi le crawler Spatie a été abandonné ; migration `fix_percentage_columns` explique pourquoi `decimal(20,4)` était nécessaire ; `SecurityHeaders` explique pourquoi il n'y a pas encore de CSP). Pas de docblocks superflus décrivant l'évident.
- **Migrations** : une migration par changement ponctuel plutôt que des migrations regroupées (ex. `add_indexes_to_cryptocurrencies_table`, `add_views_count_to_cryptocurrencies_table`, `add_sparkline_to_cryptocurrencies_table` sont 3 fichiers séparés le même jour). Nommage horodaté explicite du contenu.
- **Nommage des routes** : dot-notation `domaine.action` (`crypto.index`, `market.gainers`, `pages.about`), avec suffixe `.seo` ou nom alternatif pour les alias SEO d'une même action (voir §4).
- **i18n** : clés `__('namespace.key')` à plat dans des JSON (pas de fichiers PHP `lang/en/xxx.php` par namespace) — voir §6.
- **Dette / code mort identifié** :
  - `laravel/breeze` est une dépendance composer active mais **aucune route d'auth publique** n'existe (`routes/web.php` ne contient ni `login` ni `register`) ; les composants Blade Breeze (`components/dropdown.blade.php`, `primary-button.blade.php`, etc.) sont présents mais non référencés par les vues actives identifiées. **[À VÉRIFIER]** avant suppression — pourrait être prévu pour un futur compte utilisateur public (watchlist/alertes, cf. tables supprimées `watchlists`/`price_alerts`).
  - Modèle `News` (table `news`, RSS CryptoPanic) alimenté par cron mais sans route/vue publique — probablement un module abandonné au profit de `NewsPost` (Filament). Confirmer avant d'investir dessus.
  - `resources/views/welcome.blade.php` : vue par défaut Laravel, aucune route ne semble la servir dans `routes/web.php` — probablement morte.
  - Clés de traduction `newsletter.*` dans `lang/*.json` sans fonctionnalité newsletter active (tables supprimées).
  - `cryptoinfo.zip` et `public/public.zip` à la racine/`public/` : archives volumineuses, hors du périmètre applicatif, [À VÉRIFIER] si elles doivent être supprimées/ignorées par git.

---

## Notes pour les prochaines sessions

- Ce fichier fait ~10 sections calquées sur la demande initiale. Si une section grossit beaucoup lors d'une mise à jour, envisager de la scinder en fichier séparé et de laisser un pointeur ici plutôt que de laisser CLAUDE.md devenir illisible.
- Toujours revérifier un point marqué **[À VÉRIFIER]** en lisant le code avant de s'appuyer dessus dans une réponse — ce fichier peut devenir périmé.
