<?php

namespace App\Filament\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Pages\Page;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Notifications\Notification;
use App\Models\StoreSetting;

class ManageStoreSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Variables Globales';
    protected static ?string $title = 'Variables Globales';
    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';

    protected string $view = 'filament.pages.manage-store-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = StoreSetting::first() ?? new StoreSetting();
        $this->form->fill($settings->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de Contacto y Redes')
                    ->schema([
                        TextInput::make('store_name')->label('Nombre de la Tienda')->required(),
                        TextInput::make('whatsapp_number')->label('Número de WhatsApp (ej: +51999999999)'),
                        TextInput::make('contact_email')->label('Correo de Contacto')->email(),
                        TextInput::make('store_address')->label('Dirección Física'),
                        TextInput::make('facebook_url')->label('Facebook URL')->url(),
                        TextInput::make('instagram_url')->label('Instagram URL')->url(),
                        TextInput::make('tiktok_url')->label('TikTok URL')->url(),
                    ])->columns(2),
                Section::make('Pie de Página (Footer)')
                    ->schema([
                        \Filament\Forms\Components\Select::make('footer_theme')
                            ->label('Color de Fondo')
                            ->options([
                                'light' => 'Claro (Blanco)',
                                'dark' => 'Oscuro (Negro/Azul Oscuro)',
                            ])
                            ->default('light')
                            ->required(),
                        \Filament\Forms\Components\Repeater::make('footer_columns')
                            ->label('Columnas del Footer')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Título de la Columna (ej: Empresa)')
                                    ->required(),
                                \Filament\Forms\Components\Repeater::make('links')
                                    ->label('Enlaces')
                                    ->schema([
                                        TextInput::make('label')->label('Texto del Enlace')->required(),
                                        TextInput::make('url')->label('URL')->required(),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(1)
                                    ->addActionLabel('Agregar Enlace'),
                            ])
                            ->collapsible()
                            ->defaultItems(0)
                            ->addActionLabel('Agregar Columna'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $settings = StoreSetting::first() ?? new StoreSetting();
        $settings->fill($this->form->getState());
        $settings->save();

        Notification::make()
            ->title('Configuración guardada')
            ->success()
            ->send();
    }
}
