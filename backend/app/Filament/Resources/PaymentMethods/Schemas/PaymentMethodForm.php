<?php

namespace App\Filament\Resources\PaymentMethods\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PaymentMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('code')
                    ->required(),
                \Filament\Forms\Components\Select::make('scope')
                    ->label('Ámbito')
                    ->options([
                        'pos' => 'Caja (POS)',
                        'web' => 'Pasarela Web',
                        'both' => 'Ambos',
                    ])
                    ->default('pos')
                    ->required(),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
