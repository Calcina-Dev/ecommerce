@if ($getRecord())
    @livewire(\App\Livewire\OrderNotesList::class, ['order' => $getRecord()])
@endif
