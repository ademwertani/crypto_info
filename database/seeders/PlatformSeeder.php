<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;

/**
 * ⚠️ Factual fields (type, HQ, KYC, card support, best_for, pros/cons) are
 * generic, publicly known characteristics — safe to publish as-is. Every
 * `fee_summary` below is a PLACEHOLDER, never a verified number, and
 * `data_verified_at` is always left null. A human must check the real fee
 * schedule on each platform's official pricing page before this is ever
 * treated as accurate — see the TODO comment on every entry.
 *
 * Idempotent: firstOrCreate() never touches a row that already exists, so
 * this is safe to re-run without clobbering manual edits made in Filament.
 */
class PlatformSeeder extends Seeder
{
    private const EXCHANGE_FEE_PLACEHOLDER = 'Maker/taker fees vary by account tier and payment method — see the official pricing page for current rates.';

    private const HARDWARE_WALLET_FEE_PLACEHOLDER = 'No trading fees — this is a one-time device purchase plus normal blockchain network fees. Check the official store for current device pricing.';

    private const SOFTWARE_WALLET_FEE_PLACEHOLDER = 'Free to use for self-custody — blockchain network (gas) fees apply, and in-app buy/swap features may add partner fees.';

    public function run(): void
    {
        foreach ($this->platforms() as $data) {
            // TODO: verify manually — fee_summary is a placeholder above,
            // never a confirmed figure. Do not remove this without
            // checking the platform's real, current pricing page.
            $data['data_verified_at'] = null;

            Platform::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }

    private function platforms(): array
    {
        return [
            // ── Exchanges ────────────────────────────────────────────────
            [
                'slug' => 'binance',
                'name' => 'Binance',
                'type' => 'exchange',
                'hq_country' => 'No single official HQ (historically operated across multiple jurisdictions)',
                'requires_kyc' => true,
                'supports_cards' => true,
                'best_for' => 'Largest coin selection & liquidity',
                'pros' => [
                    'High liquidity and trading volume',
                    'Very wide selection of listed coins',
                    'Broad range of products (spot, futures, staking)',
                ],
                'cons' => [
                    'Has faced regulatory scrutiny in multiple countries',
                    'Interface can overwhelm first-time users',
                    'Large feature set most beginners will not need',
                ],
                'fee_summary' => self::EXCHANGE_FEE_PLACEHOLDER,
                'affiliate_url' => 'https://www.binance.com/en/register?ref=CRYPTOINFO',
                'status' => 'published',
            ],
            [
                'slug' => 'bybit',
                'name' => 'Bybit',
                'type' => 'exchange',
                'hq_country' => 'United Arab Emirates (Dubai)',
                'requires_kyc' => true,
                'supports_cards' => true,
                'best_for' => 'Derivatives & futures trading',
                'pros' => [
                    'Popular for derivatives and futures trading',
                    'Responsive mobile and web trading apps',
                    'Wide range of trading pairs',
                ],
                'cons' => [
                    'Not licensed or available in every country',
                    'Derivatives trading carries elevated risk',
                    'Regulatory status varies by region and can change',
                ],
                'fee_summary' => self::EXCHANGE_FEE_PLACEHOLDER,
                'affiliate_url' => 'https://www.bybit.com/en/register?affiliate_id=CRYPTOINFO',
                'status' => 'published',
            ],
            [
                'slug' => 'okx',
                'name' => 'OKX',
                'type' => 'exchange',
                'hq_country' => 'Seychelles (registered); major operations across Asia',
                'requires_kyc' => true,
                'supports_cards' => true,
                'best_for' => 'Advanced trading tools',
                'pros' => [
                    'Broad spot and derivatives market selection',
                    'Built-in Web3 wallet and DeFi access',
                    'Competitive fee structure (verify current rates)',
                ],
                'cons' => [
                    'Restricted or unavailable in some jurisdictions',
                    'Advanced tools can overwhelm casual users',
                    'Licensing status varies significantly by region',
                ],
                'fee_summary' => self::EXCHANGE_FEE_PLACEHOLDER,
                'affiliate_url' => 'https://www.okx.com/join/CRYPTOINFO',
                'status' => 'published',
            ],
            [
                'slug' => 'coinbase',
                'name' => 'Coinbase',
                'type' => 'exchange',
                'hq_country' => 'United States',
                'requires_kyc' => true,
                'supports_cards' => true,
                'best_for' => 'Beginners & US users',
                'pros' => [
                    'Publicly traded (Nasdaq: COIN), high US regulatory transparency',
                    'Beginner-friendly interface',
                    'Long operating history and strong brand recognition',
                ],
                'cons' => [
                    'Generally higher fees than offshore competitors',
                    'Smaller coin selection than some international exchanges',
                    'Customer support response times often criticized',
                ],
                'fee_summary' => self::EXCHANGE_FEE_PLACEHOLDER,
                'affiliate_url' => 'https://www.coinbase.com',
                'status' => 'published',
            ],
            [
                'slug' => 'kraken',
                'name' => 'Kraken',
                'type' => 'exchange',
                'hq_country' => 'United States',
                'requires_kyc' => true,
                'supports_cards' => true,
                'best_for' => 'Long-standing security track record',
                'pros' => [
                    'No major user-fund hack on record to date',
                    'Wide range of supported fiat currencies',
                    'Offers both simple and advanced (Pro) trading modes',
                ],
                'cons' => [
                    'Not available in every US state or country',
                    'Kraken Pro interface has a learning curve',
                    'Fewer small-cap coins listed than some competitors',
                ],
                'fee_summary' => self::EXCHANGE_FEE_PLACEHOLDER,
                'affiliate_url' => 'https://www.kraken.com',
                'status' => 'published',
            ],
            [
                'slug' => 'kucoin',
                'name' => 'KuCoin',
                'type' => 'exchange',
                'hq_country' => 'Seychelles (registered)',
                'requires_kyc' => true,
                'supports_cards' => true,
                'best_for' => 'Altcoin variety',
                'pros' => [
                    'Very wide selection of smaller and newer altcoins',
                    'Limited trading possible before completing full KYC',
                    'Frequent new token listings',
                ],
                'cons' => [
                    'Has faced regulatory action in some jurisdictions',
                    'Many listed tokens are lower-liquidity, higher-risk',
                    'Not licensed in every country',
                ],
                'fee_summary' => self::EXCHANGE_FEE_PLACEHOLDER,
                'affiliate_url' => 'https://www.kucoin.com',
                'status' => 'published',
            ],
            [
                'slug' => 'bitget',
                'name' => 'Bitget',
                'type' => 'exchange',
                'hq_country' => 'Seychelles (registered); operational hub in Singapore',
                'requires_kyc' => true,
                'supports_cards' => true,
                'best_for' => 'Copy trading',
                'pros' => [
                    'Known for copy-trading / social trading features',
                    'Wide range of derivatives products',
                    'Frequent promotional trading incentives',
                ],
                'cons' => [
                    'Shorter track record than the largest exchanges',
                    'Licensing status varies by region',
                    'Copy trading adds risk beyond normal trading risk',
                ],
                'fee_summary' => self::EXCHANGE_FEE_PLACEHOLDER,
                'affiliate_url' => 'https://www.bitget.com',
                'status' => 'published',
            ],

            // ── Wallets ──────────────────────────────────────────────────
            [
                'slug' => 'ledger',
                'name' => 'Ledger',
                'type' => 'wallet',
                'hq_country' => 'France',
                'requires_kyc' => false,
                'supports_cards' => true,
                'best_for' => 'Cold storage security',
                'pros' => [
                    'Private keys stored offline on dedicated hardware',
                    'Long track record, millions of units sold',
                    'Very wide coin support via the Ledger Live app',
                ],
                'cons' => [
                    'Requires buying a physical device',
                    'Less convenient for frequent day-to-day spending',
                    'Losing the device and recovery phrase means permanent loss',
                ],
                'fee_summary' => self::HARDWARE_WALLET_FEE_PLACEHOLDER,
                'affiliate_url' => 'https://www.ledger.com',
                'status' => 'published',
            ],
            [
                'slug' => 'trezor',
                'name' => 'Trezor',
                'type' => 'wallet',
                'hq_country' => 'Czech Republic',
                'requires_kyc' => false,
                'supports_cards' => false,
                'best_for' => 'Open-source hardware security',
                'pros' => [
                    'One of the original hardware wallets, on the market since 2014',
                    'Firmware and some software components are open-source',
                    'Private keys never leave the device',
                ],
                'cons' => [
                    'Requires buying a physical device',
                    'Smaller native coin list than some competitors',
                    'Less convenient than a mobile app for everyday use',
                ],
                'fee_summary' => self::HARDWARE_WALLET_FEE_PLACEHOLDER,
                'affiliate_url' => 'https://trezor.io',
                'status' => 'published',
            ],
            [
                'slug' => 'tangem',
                'name' => 'Tangem',
                'type' => 'wallet',
                'hq_country' => 'Switzerland',
                'requires_kyc' => false,
                'supports_cards' => false,
                'best_for' => 'Simplicity & durability',
                'pros' => [
                    'Card-shaped hardware wallet with no battery or screen to fail',
                    'Simple tap-to-use design aimed at non-technical users',
                    'Optional backup card instead of a written seed phrase',
                ],
                'cons' => [
                    'Shorter track record than Ledger or Trezor',
                    'No on-device screen to independently verify transactions',
                    'Fewer advanced features than screen-based hardware wallets',
                ],
                'fee_summary' => self::HARDWARE_WALLET_FEE_PLACEHOLDER,
                'affiliate_url' => 'https://tangem.com',
                'status' => 'published',
            ],
            [
                'slug' => 'metamask',
                'name' => 'MetaMask',
                'type' => 'wallet',
                'hq_country' => 'United States (developed by Consensys)',
                'requires_kyc' => false,
                'supports_cards' => true,
                'best_for' => 'Ethereum & dApp access',
                'pros' => [
                    'Free browser extension and mobile app',
                    'Widely supported across Ethereum/EVM dApps',
                    'Full self-custody of private keys',
                ],
                'cons' => [
                    'Software wallet — more exposed to malware/phishing than hardware',
                    'No support line to recover a lost seed phrase',
                    'Weaker native support for non-EVM chains',
                ],
                'fee_summary' => self::SOFTWARE_WALLET_FEE_PLACEHOLDER,
                'affiliate_url' => 'https://metamask.io',
                'status' => 'published',
            ],
            [
                'slug' => 'trust-wallet',
                'name' => 'Trust Wallet',
                'type' => 'wallet',
                'hq_country' => 'N/A (developed by Binance)',
                'requires_kyc' => false,
                'supports_cards' => true,
                'best_for' => 'Multi-chain mobile wallet',
                'pros' => [
                    'Free app supporting a large number of blockchains',
                    'Built-in dApp browser and swap feature',
                    'Long-standing, widely used mobile wallet',
                ],
                'cons' => [
                    'Mobile software wallet — more exposed than cold storage',
                    'Owned by Binance, a factor some users weigh',
                    'No recovery beyond the user\'s own seed phrase backup',
                ],
                'fee_summary' => self::SOFTWARE_WALLET_FEE_PLACEHOLDER,
                'affiliate_url' => 'https://trustwallet.com',
                'status' => 'published',
            ],
        ];
    }
}
