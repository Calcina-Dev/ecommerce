<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Información del Cliente')
                    ->columns(3)
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('dni')
                            ->label('DNI / RUC')
                            ->placeholder('No registrado'),
                        \Filament\Infolists\Components\TextEntry::make('name')
                            ->label('Nombre Completo / Razón Social'),
                        \Filament\Infolists\Components\TextEntry::make('email')
                            ->label('Correo Electrónico'),
                        \Filament\Infolists\Components\TextEntry::make('phone')
                            ->label('Teléfono')
                            ->placeholder('No registrado'),
                        \Filament\Infolists\Components\TextEntry::make('created_at')
                            ->label('Cliente Desde')
                            ->dateTime('d/m/Y H:i'),
                        \Filament\Infolists\Components\TextEntry::make('total_spent')
                            ->label('Total Comprado (POS + Web)')
                            ->money('PEN')
                            ->getStateUsing(function ($record) {
                                $posSales = $record->sales()->where('status', 'CONFIRMED')->sum('total_amount');
                                $onlineOrders = $record->orders()->whereNotIn('status', ['cancelled', 'failed'])->sum('total_amount');
                                return $posSales + $onlineOrders;
                            }),
                    ]),
            ]);
    }
}
