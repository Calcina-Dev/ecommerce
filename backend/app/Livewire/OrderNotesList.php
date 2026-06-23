<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\OrderNote;
use Illuminate\Support\Facades\Mail;
use App\Mail\CustomerNoteMail;

class OrderNotesList extends Component
{
    public Order $order;
    public string $content = '';
    public string $type = 'private'; // 'private' or 'customer'

    public function mount(Order $order)
    {
        $this->order = $order;
    }

    public function addNote()
    {
        $this->validate([
            'content' => 'required|string|max:1000',
            'type' => 'required|in:private,customer',
        ]);

        $note = OrderNote::create([
            'order_id' => $this->order->id,
            'user_id' => auth()->id(),
            'content' => $this->content,
            'type' => $this->type,
        ]);

        if ($this->type === 'customer' && $this->order->shipping_email) {
            try {
                Mail::to($this->order->shipping_email)->send(new CustomerNoteMail($note));
            } catch (\Exception $e) {
                error_log("Failed to send customer note email: " . $e->getMessage());
            }
        }

        $this->content = '';
        $this->type = 'private';

        // Dispara evento para mostrar una notificación en Filament si se desea
        \Filament\Notifications\Notification::make()
            ->title('Nota añadida')
            ->success()
            ->send();
    }

    public function deleteNote($noteId)
    {
        $note = OrderNote::find($noteId);
        if ($note && $note->type !== 'system') {
            $note->delete();
        }
    }

    public function render()
    {
        return view('livewire.order-notes-list', [
            'notes' => $this->order->notes()->orderBy('created_at', 'desc')->get()
        ]);
    }
}
