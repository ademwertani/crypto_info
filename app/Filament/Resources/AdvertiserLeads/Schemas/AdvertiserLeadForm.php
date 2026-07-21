<?php

namespace App\Filament\Resources\AdvertiserLeads\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdvertiserLeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->components([
                        TextInput::make('name'),
                        TextInput::make('email'),
                        TextInput::make('company'),
                        TextInput::make('budget_range')
                            ->label('Budget range'),

                        Textarea::make('message')
                            ->rows(6)
                            ->columnSpanFull(),

                        TextInput::make('ip')
                            ->label('IP address'),

                        Select::make('status')
                            ->options([
                                'new' => 'New',
                                'contacted' => 'Contacted',
                                'closed' => 'Closed',
                            ])
                            ->native(false),
                    ]),
            ]);
    }
}
