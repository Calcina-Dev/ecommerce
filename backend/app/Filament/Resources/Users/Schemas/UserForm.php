<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('dni')
                    ->label('DNI')
                    ->maxLength(15)
                    ->unique(ignoreRecord: true)
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
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Correo Electrónico')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel()
                    ->maxLength(20),
                \Filament\Forms\Components\Select::make('role')
                    ->label('Rol del Usuario')
                    ->options([
                        'admin' => 'Administrador',
                        'employee' => 'Empleado',
                        'customer' => 'Cliente',
                    ])
                    ->required()
                    ->default('customer'),
                TextInput::make('password')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255),
            ]);
    }
}
