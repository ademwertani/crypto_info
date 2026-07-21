<?php

namespace App\Filament\Resources\MoneyPages\Schemas;

use App\Models\Cryptocurrency;
use App\Models\MoneyPage;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class MoneyPageForm
{
    private const TYPES = [
        'buy_asset'       => 'Buy Asset',
        'best_list'       => 'Best List',
        'exchange_review' => 'Exchange Review',
        'wallet_review'   => 'Wallet Review',
        'how_to'          => 'How To',
        'comparison'      => 'Comparison',
    ];

    private const NETWORKS = [
        'binance' => 'Binance',
        'bybit'   => 'Bybit',
        'okx'     => 'OKX',
        'other'   => 'Other',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->components([
                        TextInput::make('h1')
                            ->label('H1 / Title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, callable $set, $get) {
                                if ($operation === 'create' && blank($get('slug'))) {
                                    $set('slug', Str::slug($state));
                                }
                            })
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(MoneyPage::class, 'slug', ignoreRecord: true)
                            ->helperText('Used in the public URL: /guides/your-slug'),

                        Select::make('type')
                            ->options(self::TYPES)
                            ->required()
                            ->native(false),

                        TextInput::make('cluster')
                            ->required()
                            ->maxLength(255)
                            ->datalist(fn () => MoneyPage::query()->whereNotNull('cluster')->distinct()->pluck('cluster')->all())
                            ->helperText('Silo/topic used for internal linking, e.g. "exchanges", "wallets".'),

                        Select::make('status')
                            ->options([
                                'draft'     => 'Draft',
                                'in_review' => 'In review',
                                'published' => 'Published',
                            ])
                            ->required()
                            ->default('draft')
                            ->native(false),

                        TextInput::make('author')
                            ->maxLength(255)
                            ->default('CryptoInfo Team'),

                        DateTimePicker::make('published_at')
                            ->label('Display date')
                            ->helperText('Pick a future date to schedule — hidden until then, even if Published.')
                            ->default(now())
                            ->required(),

                        Select::make('locale')
                            ->options([
                                'en' => 'English', 'fr' => 'Français', 'ar' => 'العربية',
                                'es' => 'Español', 'de' => 'Deutsch', 'pt' => 'Português',
                            ])
                            ->default('en')
                            ->required()
                            ->native(false),

                        TextInput::make('translation_group')
                            ->maxLength(255)
                            ->helperText('Optional. Give two pages the same value to link them as language variants of each other (drives hreflang).'),

                        TextInput::make('reading_time_min')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Leave at 0 to auto-calculate from the body word count.'),

                        Textarea::make('intro_html')
                            ->label('Intro (HTML)')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Short lede shown above the table of contents.'),

                        RichEditor::make('body_html')
                            ->label('Body')
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Use Heading 2 for sections — they auto-generate the table of contents.'),
                    ]),

                Section::make('FAQ')
                    ->collapsible()
                    ->components([
                        Repeater::make('faq')
                            ->label('')
                            ->schema([
                                TextInput::make('q')->label('Question')->required()->maxLength(255),
                                Textarea::make('a')->label('Answer')->required()->rows(2),
                            ])
                            ->columns(1)
                            ->addActionLabel('Add question')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['q'] ?? null),
                    ]),

                Section::make('Call-to-action blocks')
                    ->collapsible()
                    ->components([
                        Repeater::make('cta_config')
                            ->label('')
                            ->schema([
                                TextInput::make('label')->required()->maxLength(80),
                                TextInput::make('href')->label('URL')->required()->url(),
                                Select::make('network')->options(self::NETWORKS)->required()->native(false),
                                TextInput::make('coin')->label('Coin slug (optional)')->maxLength(255),
                                TextInput::make('placement')->label('Placement tag (optional)')->maxLength(255)
                                    ->helperText('Defaults to "guide_cta" if left blank.'),
                            ])
                            ->columns(2)
                            ->addActionLabel('Add CTA block')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null),
                    ]),

                Section::make('Internal linking')
                    ->columns(2)
                    ->collapsible()
                    ->components([
                        Select::make('related_coin_ids')
                            ->label('Related coins')
                            ->multiple()
                            ->searchable()
                            ->options(fn () => Cryptocurrency::query()->orderBy('market_cap_rank')->limit(250)->pluck('name', 'id'))
                            ->helperText('Shown as "Related coins" on the page.'),

                        Select::make('related_page_ids')
                            ->label('Related guides')
                            ->multiple()
                            ->searchable()
                            ->options(fn ($record) => MoneyPage::query()
                                ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                ->pluck('h1', 'id'))
                            ->helperText('Shown as "Related guides" on the page.'),
                    ]),

                Section::make('SEO')
                    ->columns(2)
                    ->collapsible()
                    ->components([
                        TextInput::make('meta_title')
                            ->maxLength(255)
                            ->helperText('Falls back to H1 if left empty.'),

                        TextInput::make('meta_description')
                            ->maxLength(255)
                            ->helperText('Falls back to the intro text if left empty.'),
                    ]),
            ]);
    }
}
