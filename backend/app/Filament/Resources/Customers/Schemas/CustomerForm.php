<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('dni')
                    ->label('DNI / RUC')
                    ->maxLength(15)
                    ->unique(table: 'users', column: 'dni', ignoreRecord: true)
                    ->live(debounce: 500)
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (empty($state)) return;
                        $service = app(\App\Services\PeruConsultService::class);
                        $result = null;
                        if (strlen($state) === 8) {
                            $result = $service->consultDni($state);
                        } elseif (strlen($state) === 11) {
                            $result = $service->consultRuc($state);
                        }
                        if ($result && isset($result['name'])) {
                            $set('name', $result['name']);
                            \Filament\Notifications\Notification::make()->title('Datos autocompletados')->success()->send();
                        }
                    })
                    ->suffixAction(
                        \Filament\Actions\Action::make('search')
                            ->icon('heroicon-m-magnifying-glass')
                            ->action(function ($state, callable $set) {
                                if (empty($state)) {
                                    \Filament\Notifications\Notification::make()->title('Ingrese un documento')->warning()->send();
                                    return;
                                }

                                $service = app(\App\Services\PeruConsultService::class);
                                $result = null;

                                if (strlen($state) === 8) {
                                    $result = $service->consultDni($state);
                                } elseif (strlen($state) === 11) {
                                    $result = $service->consultRuc($state);
                                } else {
                                    \Filament\Notifications\Notification::make()->title('Documento inválido')->body('Debe tener 8 o 11 dígitos')->danger()->send();
                                    return;
                                }

                                if ($result && isset($result['name'])) {
                                    $set('name', $result['name']);
                                    \Filament\Notifications\Notification::make()->title('Datos encontrados')->success()->send();
                                } else {
                                    \Filament\Notifications\Notification::make()->title('No se encontraron datos')->danger()->send();
                                }
                            })
                    ),
                \Filament\Forms\Components\TextInput::make('name')
                    ->label('Nombre Completo / Razón Social')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\TextInput::make('email')
                    ->label('Correo Electrónico')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                \Filament\Forms\Components\TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel()
                    ->maxLength(20),
            ]);
    }
}
