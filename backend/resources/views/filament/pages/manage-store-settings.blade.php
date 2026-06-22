<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 text-right">
            <x-filament::button type="submit" color="primary">
                Guardar Configuración
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
