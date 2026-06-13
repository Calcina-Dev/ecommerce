<?php

namespace App\Filament\Resources\CashSessions;

use App\Filament\Resources\CashSessions\Pages\ManageCashSessions;
use App\Models\CashSession;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CashSessionResource extends Resource
{
    protected static ?string $model = CashSession::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Turnos de Caja';
    protected static ?string $modelLabel = 'Turno de Caja';
    protected static ?string $pluralModelLabel = 'Turnos de Caja';
    protected static \UnitEnum|string|null $navigationGroup = 'Tesorería y Pagos';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('cash_register_id')
                    ->label('Caja Física')
                    ->relationship('register', 'name')
                    ->required()
                    ->disabled(fn ($context) => $context === 'edit'),
                \Filament\Forms\Components\Select::make('user_id')
                    ->label('Cajero')
                    ->relationship('user', 'name', fn (\Illuminate\Database\Eloquent\Builder $query) => $query->whereIn('role', ['admin', 'employee']))
                    ->default(auth()->id())
                    ->required()
                    ->disabled(fn ($context) => $context === 'edit'),
                \Filament\Forms\Components\TextInput::make('opening_balance')
                    ->label('Fondo Inicial (S/)')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->disabled(fn ($context) => $context === 'edit'),
                \Filament\Forms\Components\TextInput::make('closing_balance')
                    ->label('Saldo Final (Físico Contado)')
                    ->numeric()
                    ->visible(fn ($context) => $context === 'edit'),
                \Filament\Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'open' => 'Abierta',
                        'closed' => 'Cerrada',
                    ])
                    ->default('open')
                    ->visible(fn ($context) => $context === 'edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('register.name')
                    ->label('Caja')
                    ->sortable()
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('user.name')
                    ->label('Cajero')
                    ->sortable()
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('opening_balance')
                    ->label('Fondo Inicial')
                    ->formatStateUsing(fn ($state) => 'S/ ' . number_format((float)$state, 2))
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('closing_balance')
                    ->label('Monto Final Contado')
                    ->formatStateUsing(fn ($state) => $state !== null ? 'S/ ' . number_format((float)$state, 2) : '-')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'En Curso',
                        'closed' => 'Cerrado',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'success',
                        'closed' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('opened_at')
                    ->label('Apertura')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('closed_at')
                    ->label('Cierre')
                    ->dateTime('d M Y, h:i A')
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('report')
                    ->label('Ver Reporte')
                    ->icon('heroicon-o-document-chart-bar')
                    ->color('info')
                    ->modalHeading('Reporte de Cuadre de Caja')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalContent(function (CashSession $record) {
                        $paymentsBreakdown = \Illuminate\Support\Facades\DB::table('sale_payments')
                            ->join('payment_methods', 'sale_payments.payment_method_id', '=', 'payment_methods.id')
                            ->where('sale_payments.cash_session_id', $record->id)
                            ->select('payment_methods.name as method_name', \Illuminate\Support\Facades\DB::raw('SUM(sale_payments.amount) as total'))
                            ->groupBy('payment_methods.id', 'payment_methods.name')
                            ->get();

                        return view('filament.resources.cash-sessions.report', [
                            'record' => $record,
                            'paymentsBreakdown' => $paymentsBreakdown,
                        ]);
                    }),
                Action::make('close_session')
                    ->label('Cerrar Caja')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->visible(fn (CashSession $record) => $record->status === 'open' && (auth()->user()->role === 'admin' || auth()->id() === $record->user_id))
                    ->form([
                        \Filament\Forms\Components\TextInput::make('closing_balance')
                            ->label('¿Cuánto efectivo hay físicamente en la caja?')
                            ->numeric()
                            ->required(),
                    ])
                    ->action(function (CashSession $record, array $data): void {
                        $record->update([
                            'closing_balance' => $data['closing_balance'],
                            'closed_at' => now(),
                            'status' => 'closed',
                        ]);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Cerrar Turno de Caja'),
                DeleteAction::make()
                    ->before(function ($record) {
                        \Illuminate\Support\Facades\DB::table('sale_payments')
                            ->where('cash_session_id', $record->id)
                            ->update(['cash_session_id' => null]);
                        \Illuminate\Support\Facades\DB::table('cash_transactions')
                            ->where('cash_session_id', $record->id)
                            ->delete();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $ids = $records->pluck('id')->toArray();
                            \Illuminate\Support\Facades\DB::table('sale_payments')
                                ->whereIn('cash_session_id', $ids)
                                ->update(['cash_session_id' => null]);
                            \Illuminate\Support\Facades\DB::table('cash_transactions')
                                ->whereIn('cash_session_id', $ids)
                                ->delete();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCashSessions::route('/'),
        ];
    }
}
