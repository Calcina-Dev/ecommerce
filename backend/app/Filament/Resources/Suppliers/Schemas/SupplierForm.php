<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required(),
                TextInput::make('contact_name'),
                TextInput::make('email')->email(),
                TextInput::make('phone')->tel(),
                TextInput::make('address')->columnSpanFull(),
            ]);
    }
}
