@php
    $status = $getRecord()?->status ?? 'draft';
    
    $steps = [
        'draft' => ['label' => 'Borrador', 'icon' => 'heroicon-m-document'],
        'sent' => ['label' => 'Enviada', 'icon' => 'heroicon-m-paper-airplane'],
        'partial' => ['label' => 'Rec. Parcial', 'icon' => 'heroicon-m-inbox-arrow-down'],
        'completed' => ['label' => 'Completada', 'icon' => 'heroicon-m-check-badge'],
    ];

    $currentIndex = array_search($status, array_keys($steps));
    if ($currentIndex === false) {
        $currentIndex = -1; // If cancelled
    }
@endphp

<div style="width: 100%; padding-top: 1.5rem; padding-bottom: 1.5rem;">
    @if($status === 'cancelled')
        <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; color: #dc2626; background-color: #fef2f2; padding: 1rem; border-radius: 0.75rem;">
            <x-filament::icon icon="heroicon-o-x-circle" class="w-8 h-8" />
            <span style="font-size: 1.125rem; font-weight: bold;">Esta Orden de Compra fue CANCELADA</span>
        </div>
    @else
        <div style="position: relative; width: 100%;">
            <!-- Connecting Line Background -->
            <div style="position: absolute; left: 0; top: 1.25rem; width: 100%; height: 4px; background-color: #e5e7eb; border-radius: 9999px;"></div>
            
            <!-- Progress Line -->
            <div style="position: absolute; left: 0; top: 1.25rem; height: 4px; background-color: #4f46e5; border-radius: 9999px; transition: width 0.5s;" 
                 style="width: {{ $currentIndex > 0 ? ($currentIndex / (count($steps) - 1)) * 100 : 0 }}%">
            </div>

            <!-- Steps Grid -->
            <div style="display: grid; grid-template-columns: repeat({{ count($steps) }}, minmax(0, 1fr)); position: relative; z-index: 10;">
                @foreach($steps as $key => $step)
                    @php
                        $stepIndex = $loop->index;
                        $isCompleted = $stepIndex <= $currentIndex;
                        $isCurrent = $stepIndex === $currentIndex;
                    @endphp
                    <div style="display: flex; flex-direction: column; align-items: center;">
                        <!-- Circle -->
                        <div style="display: flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; border-radius: 9999px; border: 2px solid {{ $isCompleted ? '#4f46e5' : '#d1d5db' }}; background-color: {{ $isCompleted ? '#4f46e5' : '#ffffff' }}; color: {{ $isCompleted ? '#ffffff' : '#9ca3af' }}; {{ $isCurrent ? 'box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.2); transform: scale(1.1);' : '' }} transition: all 0.3s;">
                            <x-filament::icon :icon="$step['icon']" class="w-5 h-5" />
                        </div>
                        <!-- Label -->
                        <div style="margin-top: 0.75rem; text-align: center;">
                            <span style="font-size: 0.875rem; font-weight: 500; color: {{ $isCurrent ? '#4f46e5' : ($isCompleted ? '#111827' : '#9ca3af') }};">
                                {{ $step['label'] }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
