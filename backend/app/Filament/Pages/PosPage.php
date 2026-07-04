<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use App\Models\Product;
use App\Models\DocumentSeries;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class PosPage extends Page implements HasForms
{
    use InteractsWithForms, \Livewire\WithPagination;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-computer-desktop';
    protected static string|\UnitEnum|null $navigationGroup = 'Ventas';
    protected static ?string $navigationLabel = 'Terminal POS';
    protected static ?string $title = 'Terminal POS (Punto de Venta)';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.pos-page';

    // Para la búsqueda de productos
    public string $searchQuery = '';

    // Estado del carrito
    public array $cart = [];
    public float $subtotal = 0;
    public float $total_tax = 0;
    public float $total_amount = 0;

    // Estado del formulario de venta
    public ?array $checkoutData = [];

    // Sesión de caja activa
    public ?\App\Models\CashSession $activeSession = null;
    public float $openingBalance = 0.00;
    public ?int $selectedRegisterId = null;

    public ?int $selectedWarehouseId = null;

    public function mount()
    {
        $this->selectedWarehouseId = \App\Models\Warehouse::first()?->id;

        $this->activeSession = \App\Models\CashSession::where('user_id', auth()->id())
            ->where('status', 'open')
            ->first() ?? \App\Models\CashSession::where('status', 'open')->first();

        if (!$this->activeSession) {
            $this->selectedRegisterId = \App\Models\CashRegister::where('is_active', true)->first()?->id;
        }

        $this->form->fill([
            'document_type' => DocumentSeries::where('is_active', true)->where('document_type', 'BOLETA')->exists() ? 'BOLETA' : null,
            'payments' => [
                ['payment_method_id' => null, 'amount' => null, 'reference' => null]
            ],
        ]);
    }

    public function openSession()
    {
        $this->validate([
            'openingBalance' => 'required|numeric|min:0',
        ], [
            'openingBalance.required' => 'El monto inicial es obligatorio.',
            'openingBalance.numeric' => 'El monto inicial debe ser un número.',
            'openingBalance.min' => 'El monto inicial no puede ser negativo.',
        ]);

        $register = \App\Models\CashRegister::firstOrCreate(['name' => 'Caja Principal']);

        $this->activeSession = \App\Models\CashSession::create([
            'cash_register_id' => $this->selectedRegisterId ?? $register->id,
            'user_id' => auth()->id() ?? 1,
            'opening_balance' => $this->openingBalance,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        Notification::make()
            ->title('Turno Abierto Exitosamente')
            ->body("Caja abierta con S/ " . number_format($this->openingBalance, 2))
            ->success()
            ->send();
    }

    // Reiniciar paginación al buscar o cambiar almacén
    public function updatedSearchQuery()
    {
        $this->resetPage();
    }

    public function updatedSelectedWarehouseId()
    {
        $this->resetPage();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('customer_id')
                    ->label('Cliente (DNI o Nombre)')
                    ->options(function () {
                        return \App\Models\User::all()->mapWithKeys(function ($user) {
                            $label = $user->dni ? "{$user->dni} - {$user->name}" : $user->name;
                            return [$user->id => $label];
                        });
                    })
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('dni')
                            ->label('DNI')
                            ->required()
                            ->maxLength(15)
                            ->unique(table: 'users', column: 'dni')
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
                            ->label('Nombre Completo')
                            ->required(),
                        TextInput::make('phone')
                            ->label('Celular')
                            ->tel(),
                        TextInput::make('email')
                            ->label('Correo Electrónico (Opcional)')
                            ->email(),
                    ])
                    ->createOptionUsing(function (array $data) {
                        $user = \App\Models\User::create([
                            'dni' => $data['dni'],
                            'name' => $data['name'],
                            'phone' => $data['phone'] ?? null,
                            'email' => $data['email'] ?? ($data['dni'] . '@cliente.local'),
                            'password' => bcrypt($data['dni']),
                        ]);
                        return $user->id;
                    })
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $user = \App\Models\User::find($state);
                            if ($user && !str_ends_with($user->email, '@cliente.local')) {
                                $set('customer_email', $user->email);
                            }
                        } else {
                            $set('customer_email', null);
                        }
                    })
                    ->placeholder('Consumidor Final'),
                
                TextInput::make('customer_email')
                    ->label('Correo para Boleta (Opcional)')
                    ->email()
                    ->placeholder('ejemplo@correo.com'),

                Select::make('document_type')
                    ->label('Comprobante')
                    ->options(function () {
                        return DocumentSeries::where('is_active', true)
                            ->whereIn('document_type', ['BOLETA', 'FACTURA', 'TICKET'])
                            ->pluck('document_type', 'document_type')
                            ->mapWithKeys(fn ($type) => [$type => ucfirst(strtolower($type))]);
                    })
                    ->required(),

                Repeater::make('payments')
                    ->label('Pagos')
                    ->schema([
                        Select::make('payment_method_id')
                            ->label('Método')
                            ->options(function () {
                                return \App\Models\PaymentMethod::pluck('name', 'id');
                            })
                            ->required()
                            ->columnSpan(1),
                        TextInput::make('amount')
                            ->label('Monto (S/)')
                            ->numeric()
                            ->default(function ($get) {
                                $total = $this->total_amount;
                                $payments = $get('../../payments');
                                $paid = 0;
                                if (is_array($payments)) {
                                    foreach ($payments as $payment) {
                                        $paid += (float)($payment['amount'] ?? 0);
                                    }
                                }
                                $remaining = $total - $paid;
                                return $remaining > 0 ? $remaining : 0;
                            })
                            ->placeholder('Total')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->addActionLabel('Añadir método de pago')
                    ->defaultItems(1),
            ])
            ->statePath('checkoutData')
            ->columns(1);
    }

    #[\Livewire\Attributes\Computed]
    public function products()
    {
        $warehouseId = $this->selectedWarehouseId ?? 1;

        return Product::where('is_active', true)
            ->whereHas('stockBalances', function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId)
                      ->where('on_hand', '>', 0);
            })
            ->when($this->searchQuery, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->searchQuery . '%')
                      ->orWhere('sku', 'like', '%' . $this->searchQuery . '%');
                });
            })
            ->addSelect(['*', 'warehouse_stock' => \App\Models\StockBalance::selectRaw('COALESCE(SUM(on_hand), 0)')
                ->whereColumn('product_id', 'products.id')
                ->where('warehouse_id', $warehouseId)
            ])
            ->paginate(9);
    }

    public function addToCart(int $productId)
    {
        $product = Product::find($productId);
        if (!$product) return;

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
        } else {
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
            ];
        }

        $this->calculateTotals();
    }

    public function removeFromCart(int $productId)
    {
        unset($this->cart[$productId]);
        $this->calculateTotals();
    }

    public function increaseQuantity(int $productId)
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
            $this->calculateTotals();
        }
    }

    public function decreaseQuantity(int $productId)
    {
        if (isset($this->cart[$productId])) {
            if ($this->cart[$productId]['quantity'] > 1) {
                $this->cart[$productId]['quantity']--;
            } else {
                unset($this->cart[$productId]);
            }
            $this->calculateTotals();
        }
    }

    private function calculateTotals()
    {
        $this->total_amount = 0;
        foreach ($this->cart as $item) {
            $this->total_amount += ($item['price'] * $item['quantity']);
        }
        $this->subtotal = round($this->total_amount / 1.18, 2);
        $this->total_tax = round($this->total_amount - $this->subtotal, 2);
    }

    public function checkout()
    {
        if (!$this->activeSession) {
            Notification::make()->title('No hay turno abierto')->body('Debes abrir un turno de caja antes de cobrar.')->danger()->send();
            return;
        }

        if (empty($this->cart)) {
            Notification::make()->title('El carrito está vacío')->danger()->send();
            return;
        }

        $data = $this->form->getState();

        try {
            DB::beginTransaction();

            // 1. Crear la Venta
            $sale = Sale::create([
                'user_id' => auth()->id() ?? 1,
                'customer_id' => $data['customer_id'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'warehouse_id' => $this->selectedWarehouseId ?? \App\Models\Warehouse::first()?->id ?? 1,
                'document_type' => $data['document_type'],
                'subtotal' => $this->subtotal,
                'total_tax' => $this->total_tax,
                'total_amount' => $this->total_amount,
                'status' => 'CONFIRMED',
            ]);

            // 2. Crear los Items
            foreach ($this->cart as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => 0, // Esto se actualiza en el deductStockFEFO
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            // 2.5 Deducir Stock
            $inventoryService = new \App\Services\InventoryService();
            $sale->refresh();
            $totalCost = $inventoryService->deductStockFEFO(
                $sale->items,
                $sale->warehouse_id,
                'SALE',
                $sale->id,
                $sale->document_number ?? (string)$sale->id,
                $sale->user_id
            );
            $sale->updateQuietly(['total_cost' => $totalCost]);

            // 3. Crear los Pagos
            $remaining = $this->total_amount;
            if (!empty($data['payments']) && is_array($data['payments'])) {
                foreach ($data['payments'] as $payment) {
                    if (!empty($payment['payment_method_id'])) {
                        // Si el monto está vacío, asumimos que intenta pagar el resto
                        $amount = !empty($payment['amount']) ? (float)$payment['amount'] : $remaining;
                        
                        SalePayment::create([
                            'sale_id' => $sale->id,
                            'payment_method_id' => $payment['payment_method_id'],
                            'amount' => $amount,
                            'reference' => $payment['reference'] ?? null,
                            'cash_session_id' => $this->activeSession->id,
                        ]);
                        
                        $remaining -= $amount;
                        if ($remaining < 0) $remaining = 0;
                    }
                }
            }

            DB::commit();

            Notification::make()
                ->title('Venta completada con éxito')
                ->success()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('print')
                        ->label('Imprimir Ticket')
                        ->icon('heroicon-o-printer')
                        ->url(route('sale.ticket', $sale->id), shouldOpenInNewTab: true)
                        ->button(),
                ])
                ->send();

            // Limpiar carrito y formulario
            $this->cart = [];
            $this->calculateTotals();
            $this->form->fill([
                'document_type' => $data['document_type'], // Keep document type selection
                'payments' => [
                    ['payment_method_id' => null, 'amount' => null, 'reference' => null]
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title('Error al procesar la venta')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
