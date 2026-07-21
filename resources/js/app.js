import Alpine from 'alpinejs';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

globalThis.Alpine = Alpine;
globalThis.Pusher  = Pusher;

// ── Binance slug → trading pair mapping (top-100 by market cap) ───────────
const SLUG_TO_BINANCE = {
    'bitcoin':              'BTCUSDT',
    'ethereum':             'ETHUSDT',
    'binancecoin':          'BNBUSDT',
    'solana':               'SOLUSDT',
    'ripple':               'XRPUSDT',
    'the-open-network':     'TONUSDT',
    'dogecoin':             'DOGEUSDT',
    'cardano':              'ADAUSDT',
    'avalanche-2':          'AVAXUSDT',
    'shiba-inu':            'SHIBUSDT',
    'tron':                 'TRXUSDT',
    'bitcoin-cash':         'BCHUSDT',
    'chainlink':            'LINKUSDT',
    'polkadot':             'DOTUSDT',
    'near':                 'NEARUSDT',
    'litecoin':             'LTCUSDT',
    'matic-network':        'MATICUSDT',
    'internet-computer':    'ICPUSDT',
    'uniswap':              'UNIUSDT',
    'ethereum-classic':     'ETCUSDT',
    'cosmos':               'ATOMUSDT',
    'stellar':              'XLMUSDT',
    'filecoin':             'FILUSDT',
    'lido-dao':             'LDOUSDT',
    'injective-protocol':   'INJUSDT',
    'arbitrum':             'ARBUSDT',
    'optimism':             'OPUSDT',
    'the-sandbox':          'SANDUSDT',
    'decentraland':         'MANAUSDT',
    'aptos':                'APTUSDT',
    'sui':                  'SUIUSDT',
    'aave':                 'AAVEUSDT',
    'maker':                'MKRUSDT',
    'vechain':              'VETUSDT',
    'hedera-hashgraph':     'HBARUSDT',
    'eos':                  'EOSUSDT',
    'algorand':             'ALGOUSDT',
    'tezos':                'XTZUSDT',
    'fantom':               'FTMUSDT',
    'axie-infinity':        'AXSUSDT',
    'the-graph':            'GRTUSDT',
    'pepe':                 'PEPEUSDT',
    'bonk':                 'BONKUSDT',
    'worldcoin-wld':        'WLDUSDT',
    'render-token':         'RENDERUSDT',
    'chiliz':               'CHZUSDT',
    'floki':                'FLOKIUSDT',
    'gala':                 'GALAUSDT',
    'blur':                 'BLURUSDT',
    'fetch-ai':             'FETUSDT',
    'thorchain':            'RUNEUSDT',
    'quant-network':        'QNTUSDT',
    'bitcoin-sv':           'BSVUSDT',
    'stacks':               'STXUSDT',
    'kaspa':                'KASUSDT',
    'immutable-x':          'IMXUSDT',
    'mantle':               'MNTUSDT',
    'sei-network':          'SEIUSDT',
    'celestia':             'TIAUSDT',
    'dydx-chain':           'DYDXUSDT',
    'pyth-network':         'PYTHUSDT',
    'bittensor':            'TAOUSDT',
    'akash-network':        'AKTUSDT',
};

// Reverse map: 'BTCUSDT' → 'bitcoin'
const BINANCE_TO_SLUG = Object.fromEntries(
    Object.entries(SLUG_TO_BINANCE).map(([slug, sym]) => [sym, slug])
);

// ── Binance WebSocket class ───────────────────────────────────────────────
class BinanceWS {
    constructor(store) {
        this.store    = store;
        this.ws       = null;
        this.retry    = 0;
        this.maxRetry = 8;
        this.timer    = null;
    }

    connect() {
        const symbols = Object.values(SLUG_TO_BINANCE);
        const streams = symbols.map(s => `${s.toLowerCase()}@ticker`).join('/');
        const url     = `wss://stream.binance.com:9443/stream?streams=${streams}`;

        try {
            this.ws = new WebSocket(url);
        } catch (err) {
            console.warn('[BinanceWS] Cannot open WebSocket:', err.message);
            this._scheduleReconnect();
            return;
        }

        this.ws.onopen = () => {
            this.retry = 0;
            this.store.binanceConnected = true;
        };

        this.ws.onmessage = (evt) => {
            try {
                const packet = JSON.parse(evt.data);
                const d = packet?.data;
                if (!d?.s) return;

                const slug = BINANCE_TO_SLUG[d.s];
                if (!slug) return;

                const price   = Number.parseFloat(d.c);  // last/close price
                const open    = Number.parseFloat(d.o);  // 24h open
                const high    = Number.parseFloat(d.h);
                const low     = Number.parseFloat(d.l);
                const volQuot = Number.parseFloat(d.q);  // 24h volume in USDT
                const chg24h  = open > 0 ? ((price - open) / open) * 100 : 0;

                const prevPrice = this.store.prices[slug]?.price ?? price;
                let direction;
                if (price > prevPrice)       direction = 'up';
                else if (price < prevPrice)  direction = 'down';
                else                         direction = this.store.prices[slug]?.direction ?? '';

                this.store.prices[slug] = {
                    price,
                    open24h:    open,
                    change_24h: chg24h,
                    volume_24h: volQuot,
                    high_24h:   high,
                    low_24h:    low,
                    direction,
                    ts: Date.now(),
                };
            } catch (err) {
                console.warn('[BinanceWS] Message parse error:', err.message);
            }
        };

        this.ws.onerror = (err) => {
            console.warn('[BinanceWS] Error:', err.type);
        };

        this.ws.onclose = () => {
            this.store.binanceConnected = false;
            this._scheduleReconnect();
        };
    }

    _scheduleReconnect() {
        if (this.retry >= this.maxRetry) return;
        clearTimeout(this.timer);
        const delay = Math.min(1000 * 2 ** this.retry, 30_000);
        this.retry++;
        this.timer = setTimeout(() => this.connect(), delay);
    }

    disconnect() {
        clearTimeout(this.timer);
        if (this.ws) {
            this.ws.onclose = null;
            this.ws.close();
        }
    }
}

// ── Laravel Echo / Reverb (fallback — syncs on every DB fetch cycle) ──────
globalThis.Echo = new Echo({
    broadcaster:       'reverb',
    key:               import.meta.env.VITE_REVERB_APP_KEY,
    wsHost:            import.meta.env.VITE_REVERB_HOST,
    wsPort:            import.meta.env.VITE_REVERB_PORT    ?? 8080,
    wssPort:           import.meta.env.VITE_REVERB_PORT    ?? 443,
    forceTLS:          (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
    disableStats:      true,
});

// ── GDPR cookie consent ────────────────────────────────────────────────────
// Decision lives in localStorage only (no server-side cookie). GA4/Clarity
// script tags are never present in the initial HTML (see
// resources/views/components/analytics.blade.php) — they're injected here,
// on demand, only once the visitor has explicitly accepted analytics.
const CONSENT_KEY = 'cryptoinfo_consent';

function readConsent() {
    try {
        const raw = localStorage.getItem(CONSENT_KEY);
        return raw ? JSON.parse(raw) : null;
    } catch {
        return null;
    }
}

function writeConsent(analytics) {
    const value = { essential: true, analytics, ts: Date.now(), v: 1 };
    try {
        localStorage.setItem(CONSENT_KEY, JSON.stringify(value));
    } catch {
        // localStorage unavailable (private mode / quota) — decision won't
        // persist across reloads, but nothing loads without it either way.
    }
    return value;
}

function loadGa4(id) {
    if (document.getElementById('ga4-gtag')) return;

    window.dataLayer = window.dataLayer || [];
    window.gtag = window.gtag || function gtag() { window.dataLayer.push(arguments); };
    window.gtag('js', new Date());
    window.gtag('config', id, { anonymize_ip: true });

    const script = document.createElement('script');
    script.id    = 'ga4-gtag';
    script.async = true;
    script.src   = `https://www.googletagmanager.com/gtag/js?id=${id}`;
    document.head.appendChild(script);
}

function loadClarity(id) {
    if (window.clarity || document.getElementById('ms-clarity')) return;

    window.clarity = window.clarity || function clarity() {
        (window.clarity.q = window.clarity.q || []).push(arguments);
    };

    const script = document.createElement('script');
    script.id    = 'ms-clarity';
    script.async = true;
    script.src   = `https://www.clarity.ms/tag/${id}`;
    document.head.appendChild(script);
}

// Only ever called after consent is granted. window.CryptoInfoConfig is set
// by <x-analytics/> and simply doesn't exist when IDs are unset or the app
// is running in the local environment — so this silently no-ops there too.
function loadAnalyticsIfConfigured() {
    const cfg = window.CryptoInfoConfig;
    if (!cfg) return;
    if (cfg.ga4Id) loadGa4(cfg.ga4Id);
    if (cfg.clarityId) loadClarity(cfg.clarityId);
}

Alpine.store('consent', {
    show: false,
    analytics: false,

    init() {
        const saved = readConsent();
        if (saved) {
            this.analytics = !!saved.analytics;
            this.show = false;
            if (this.analytics) loadAnalyticsIfConfigured();
        } else {
            this.show = true;
        }
    },

    accept() {
        writeConsent(true);
        this.analytics = true;
        this.show = false;
        loadAnalyticsIfConfigured();
    },

    reject() {
        writeConsent(false);
        this.analytics = false;
        this.show = false;
    },

    // Reopens the banner — bound to the footer "Cookies" link.
    open() {
        this.show = true;
    },
});

// ── Theme (dark / light) store ───────────────────────────────────────────
Alpine.store('theme', {
    dark: localStorage.getItem('theme') !== 'light',
    toggle() {
        this.dark = !this.dark;
        localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        document.documentElement.classList.toggle('dark', this.dark);
        document.documentElement.classList.toggle('light', !this.dark);
    },
    init() {
        document.documentElement.classList.toggle('dark', this.dark);
        document.documentElement.classList.toggle('light', !this.dark);
    },
});

// ── Alpine live-prices store ──────────────────────────────────────────────
Alpine.store('liveprices', {
    prices:           {},
    binanceConnected: false,
    reverbConnected:  false,
    lastSync:         null,
    _binanceWS:       null,

    // true when at least one feed is up
    get connected() { return this.binanceConnected || this.reverbConnected; },

    init() {
        // Primary: Binance WebSocket (sub-second)
        this._binanceWS = new BinanceWS(this);
        this._binanceWS.connect();

        // Secondary: Reverb broadcast on every DB sync (~10 min)
        // Covers coins absent from Binance (stablecoins, newer tokens)
        globalThis.Echo.channel('crypto-prices')
            .listen('.price.updated', (data) => {
                this.reverbConnected = true;
                this.lastSync        = data.updated_at;
                data.coins.forEach(coin => {
                    if (!SLUG_TO_BINANCE[coin.slug]) {
                        this.prices[coin.slug] = {
                            price:      coin.price,
                            change_24h: coin.change_24h,
                            direction:  '',
                            ts:         Date.now(),
                        };
                    }
                });
            });
    },

    // Convenience getters used by Blade views
    get(slug)       { return this.prices[slug] ?? null; },
    price(slug)     { return this.prices[slug]?.price ?? null; },
    change24h(slug) { return this.prices[slug]?.change_24h ?? null; },
    direction(slug) { return this.prices[slug]?.direction ?? ''; },
});

// ── Analytics event helpers ────────────────────────────────────────────────
// Every call is a no-op unless GA4 is actually loaded (i.e. consent was
// granted and we're outside the local env — see loadGa4() above). In dev,
// unsent events are logged instead so affiliate-click / money-page wiring
// can be verified without a live GA4 property.
window.CryptoInfoAnalytics = {
    track(event, params = {}) {
        if (typeof window.gtag === 'function') {
            window.gtag('event', event, params);
        } else if (import.meta.env.DEV) {
            console.debug('[analytics] not sent (no consent yet, or local env):', event, params);
        }
    },

    trackAffiliateClick({ network, placement, coin }) {
        this.track('affiliate_click', {
            network,
            placement,
            coin: coin || undefined,
            page_path: location.pathname,
            locale: document.documentElement.lang,
        });
    },

    trackMoneyPageView({ page_type, coin } = {}) {
        this.track('money_page_view', {
            page_type,
            coin: coin || undefined,
            page_path: location.pathname,
            locale: document.documentElement.lang,
        });
    },

    // Placeholder — no newsletter form exists yet, kept here so the form can
    // call this without any further analytics wiring once it's built.
    trackNewsletterSignup({ placement } = {}) {
        this.track('newsletter_signup', {
            placement,
            page_path: location.pathname,
            locale: document.documentElement.lang,
        });
    },
};

// Delegated so it also covers affiliate links rendered after this script
// runs (e.g. inside Alpine x-show/x-for blocks). No preventDefault: the link
// already opens in a new tab via target="_blank".
document.addEventListener('click', (evt) => {
    const link = evt.target.closest('[data-affiliate-network]');
    if (!link) return;

    window.CryptoInfoAnalytics.trackAffiliateClick({
        network:   link.dataset.affiliateNetwork,
        placement: link.dataset.affiliatePlacement,
        coin:      link.dataset.affiliateCoin,
    });
});

Alpine.start();
