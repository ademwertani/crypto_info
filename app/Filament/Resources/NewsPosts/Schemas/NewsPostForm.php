<?php

namespace App\Filament\Resources\NewsPosts\Schemas;

use App\Models\NewsPost;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class NewsPostForm
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
                            ->unique(NewsPost::class, 'slug', ignoreRecord: true)
                            ->helperText('Used in the public URL: /news/your-slug'),

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
                            ->helperText('The date shown on the site. Pick a future date to schedule the post — it stays hidden until then, even if Published.')
                            ->default(now())
                            ->required()
                            ->columnSpanFull(),

                        Textarea::make('excerpt')
                            ->maxLength(300)
                            ->rows(2)
                            ->helperText('Short summary shown on the News list page (max 300 characters).')
                            ->columnSpanFull(),

                        RichEditor::make('content')
                            ->required()
                            ->columnSpanFull(),
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

                Section::make('Source')
                    ->description('Only set on posts drafted by `news:generate` from a real RSS item — leave blank for hand-written posts.')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(fn (?NewsPost $record) => blank($record?->source_url))
                    ->components([
                        TextInput::make('source_name')
                            ->maxLength(255),

                        TextInput::make('source_url')
                            ->label('Source URL')
                            ->url()
                            ->maxLength(255),
                    ]),
            ]);
    }
}
