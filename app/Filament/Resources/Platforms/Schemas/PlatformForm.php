<?php

namespace App\Filament\Resources\Platforms\Schemas;

use App\Models\Platform;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PlatformForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, callable $set, callable $get) {
                                if ($operation === 'create' && blank($get('slug'))) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Platform::class, 'slug', ignoreRecord: true),

                        Select::make('type')
                            ->options([
                                'exchange' => 'Exchange',
                                'wallet' => 'Wallet',
                            ])
                            ->required()
                            ->native(false),

                        TextInput::make('hq_country')
                            ->label('HQ country')
                            ->maxLength(255),

                        Toggle::make('requires_kyc')
                            ->label('Requires KYC')
                            ->default(true),

                        Toggle::make('supports_cards')
                            ->label('Supports card payments'),

                        TextInput::make('best_for')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Short phrase — this is what drives the click, e.g. "Beginners", "Low fees", "Advanced trading", "Maximum security".')
                            ->columnSpanFull(),

                        TagsInput::make('pros')
                            ->required()
                            ->helperText('One short, honest, generic claim per tag — press Enter to add.')
                            ->columnSpanFull(),

                        TagsInput::make('cons')
                            ->required()
                            ->columnSpanFull(),

                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                            ])
                            ->required()
                            ->default('published')
                            ->native(false),

                        TextInput::make('affiliate_url')
                            ->label('Affiliate / official URL')
                            ->url()
                            ->maxLength(255),
                    ]),

                Section::make('Fees — verify before trusting')
                    ->description("fee_summary is a placeholder until a human confirms it. Never set data_verified_at without actually checking the platform's current pricing page.")
                    ->collapsible()
                    ->columns(2)
                    ->components([
                        TextInput::make('fee_summary')
                            ->label('Fee summary (placeholder)')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        DateTimePicker::make('data_verified_at')
                            ->label('Fee data verified at')
                            ->helperText('Leave empty until someone has manually checked the real fee schedule.'),
                    ]),
            ]);
    }
}
