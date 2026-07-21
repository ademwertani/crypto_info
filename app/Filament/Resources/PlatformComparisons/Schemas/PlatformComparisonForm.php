<?php

namespace App\Filament\Resources\PlatformComparisons\Schemas;

use App\Models\Platform;
use App\Models\PlatformComparison;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PlatformComparisonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->components([
                        Select::make('platform_a_id')
                            ->label('Platform A')
                            ->options(fn () => Platform::orderBy('name')->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (callable $set, callable $get) => self::maybeSetSlug($set, $get)),

                        Select::make('platform_b_id')
                            ->label('Platform B')
                            ->options(fn () => Platform::orderBy('name')->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (callable $set, callable $get) => self::maybeSetSlug($set, $get)),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(PlatformComparison::class, 'slug', ignoreRecord: true)
                            ->helperText('Used in the public URL anchor, e.g. binance-vs-bybit.')
                            ->columnSpanFull(),

                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                            ])
                            ->required()
                            ->default('published')
                            ->native(false),

                        DateTimePicker::make('published_at')
                            ->default(now())
                            ->required(),

                        RichEditor::make('verdict_html')
                            ->label('Verdict (~150 words, nuanced and honest — no invented advantage)')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('SEO')
                    ->columns(2)
                    ->collapsible()
                    ->components([
                        TextInput::make('meta_title')->maxLength(255),
                        TextInput::make('meta_description')->maxLength(255),
                    ]),
            ]);
    }

    private static function maybeSetSlug(callable $set, callable $get): void
    {
        $a = Platform::find($get('platform_a_id'));
        $b = Platform::find($get('platform_b_id'));

        if ($a && $b && blank($get('slug'))) {
            $set('slug', "{$a->slug}-vs-{$b->slug}");
        }
    }
}
