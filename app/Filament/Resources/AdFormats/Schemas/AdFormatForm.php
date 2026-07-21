<?php

namespace App\Filament\Resources\AdFormats\Schemas;

use App\Models\AdFormat;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AdFormatForm
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
                            ->unique(AdFormat::class, 'slug', ignoreRecord: true),

                        Textarea::make('description')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        TagsInput::make('specs')
                            ->label('Specs (one short bullet per tag)')
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('price_range')
                            ->label('Price range')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Shown as-is on the public /advertise page — editable here any time without touching code.')
                            ->columnSpanFull(),

                        TextInput::make('sort_order')
                            ->label('Sort order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first.'),

                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                            ])
                            ->required()
                            ->default('published')
                            ->native(false),
                    ]),
            ]);
    }
}
