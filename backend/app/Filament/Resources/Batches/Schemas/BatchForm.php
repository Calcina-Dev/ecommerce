<?php

namespace App\Filament\Resources\Batches\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('product_id')
                    ->required()
                    ->numeric(),
                TextInput::make('batch_number')
                    ->required(),
                DatePicker::make('expiration_date'),
                TextInput::make('status')
                    ->required()
                    ->default('active'),
            ]);
    }
}
