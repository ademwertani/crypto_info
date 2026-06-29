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

Alpine.start();
