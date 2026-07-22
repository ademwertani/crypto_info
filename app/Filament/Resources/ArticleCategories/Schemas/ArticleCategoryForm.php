<?php

namespace App\Filament\Resources\ArticleCategories\Schemas;

use App\Models\ArticleCategory;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArticleCategoryForm
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
                            ->afterStateUpdated(function (string $operation, $state, callable $set, $get) {
                                if ($operation === 'create' && blank($get('slug'))) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ArticleCategory::class, 'slug', ignoreRecord: true)
                            ->helperText('Used in /blog?category=your-slug'),

                        Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
