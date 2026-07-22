<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Cryptocurrency;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->components([
                        TextInput::make('title')
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
                            ->unique(Article::class, 'slug', ignoreRecord: true)
                            ->helperText('Used in the public URL: /blog/your-slug'),

                        Select::make('article_category_id')
                            ->label('Category')
                            ->options(fn () => ArticleCategory::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->native(false),

                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                            ])
                            ->required()
                            ->default('draft')
                            ->native(false),

                        DateTimePicker::make('published_at')
                            ->label('Display date')
                            ->helperText('The date shown on the site. Pick a future date to schedule — hidden until then, even if Published.')
                            ->default(now())
                            ->required(),

                        TextInput::make('author_name')
                            ->label('Author')
                            ->maxLength(255)
                            ->default('CryptoInfo Team'),

                        Textarea::make('excerpt')
                            ->maxLength(300)
                            ->rows(2)
                            ->helperText('Short summary shown on the Blog list page (max 300 characters).')
                            ->columnSpanFull(),

                        TextInput::make('cover_image_url')
                            ->label('Cover image URL')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('related_coin_slugs')
                            ->label('Related coins')
                            ->multiple()
                            ->searchable()
                            ->options(fn () => Cryptocurrency::query()->orderBy('market_cap_rank')->limit(250)->pluck('name', 'slug'))
                            ->helperText('Shown as "Mentioned in this article" on the page.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Content')
                    ->description('Each block becomes one section of the article body, rendered top to bottom. Write raw HTML (e.g. <h2>...</h2><p>...</p>) — same convention as the "Intro" field on Money Pages.')
                    ->components([
                        Repeater::make('sections')
                            ->label('')
                            ->simple(
                                Textarea::make('html')->label('Section HTML')->rows(6)->required()
                            )
                            ->addActionLabel('Add section')
                            ->reorderable()
                            ->required(),
                    ]),

                Section::make('SEO')
                    ->columns(2)
                    ->collapsible()
                    ->components([
                        TextInput::make('meta_title')
                            ->maxLength(255)
                            ->helperText('Falls back to the title if left empty.'),

                        TextInput::make('meta_description')
                            ->maxLength(255)
                            ->helperText('Falls back to the excerpt if left empty.'),
                    ]),
            ]);
    }
}
