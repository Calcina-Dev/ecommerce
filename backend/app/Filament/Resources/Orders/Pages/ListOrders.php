<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Orden Manual'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Activas' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', '!=', 'pending_payment')),
            'Pendientes de Pago' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'pending_payment')->where('created_at', '>=', now()->subHours(1))),
            'Carritos Abandonados' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'pending_payment')->where('created_at', '<', now()->subHours(1))),
            'Todas' => \Filament\Schemas\Components\Tabs\Tab::make(),
        ];
    }
}
