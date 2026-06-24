<div>
    <style>
        .premium-notes-container { font-family: inherit; }
        .note-input { width: 100%; border: 1px solid #d1d5db; border-radius: 0.75rem; padding: 0.875rem; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); transition: all 0.2s ease; background-color: #fff; color: #111827; }
        .note-input:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15); }
        .note-select { border: 1px solid #d1d5db; border-radius: 0.5rem; padding: 0.625rem 2rem 0.625rem 1rem; background-color: #fff; transition: all 0.2s ease; color: #374151; font-weight: 500; appearance: none; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; }
        .note-select:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15); }
        .note-btn { display: inline-flex; align-items: center; justify-content: center; border-radius: 0.5rem; padding: 0.625rem 1.5rem; font-weight: 600; color: #fff; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); transition: all 0.3s ease; border: none; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.2); }
        .note-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 10px -1px rgba(59, 130, 246, 0.3); }
        .note-btn:active { transform: translateY(0); }
        
        .note-timeline { position: relative; padding-left: 2rem; border-left: 2px solid #e5e7eb; margin-left: 1.5rem; margin-top: 1.5rem; padding-bottom: 1.5rem; }
        .note-timeline:last-child { border-left-color: transparent; }
        .note-dot { position: absolute; left: -0.4rem; top: 1.25rem; width: 0.75rem; height: 0.75rem; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 0 2px #fff; }
        .note-dot.system { background-color: #9ca3af; box-shadow: 0 0 0 2px #f3f4f6; }
        .note-dot.private { background-color: #3b82f6; box-shadow: 0 0 0 2px #eff6ff; }
        .note-dot.customer { background-color: #10b981; box-shadow: 0 0 0 2px #ecfdf5; }
        
        .note-card { border-radius: 1rem; padding: 1.25rem; transition: all 0.3s ease; position: relative; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05); }
        .note-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -4px rgba(0, 0, 0, 0.05); }
        .note-card.system { background: linear-gradient(145deg, #ffffff, #f9fafb); border: 1px solid #f3f4f6; }
        .note-card.private { background: linear-gradient(145deg, #ffffff, #eff6ff); border: 1px solid #bfdbfe; }
        .note-card.customer { background: linear-gradient(145deg, #ffffff, #f0fdf4); border: 1px solid #bbf7d0; }
        
        .note-badge { display: inline-flex; align-items: center; border-radius: 9999px; padding: 0.25rem 0.75rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
        .note-badge.customer { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .note-badge.private { background-color: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
        
        .note-meta { font-size: 0.75rem; color: #6b7280; display: flex; align-items: center; gap: 0.5rem; margin-top: 1rem; padding-top: 0.75rem; border-top: 1px dashed rgba(156, 163, 175, 0.3); }
        
        .note-delete { color: #ef4444; background: none; border: none; cursor: pointer; font-size: 0.75rem; font-weight: 500; transition: color 0.2s; padding: 0; }
        .note-delete:hover { color: #b91c1c; text-decoration: underline; }
    </style>
    
    <div class="premium-notes-container bg-gray-50/50 dark:bg-gray-900/50">
        <!-- Formulario para añadir nota -->
        <div class="p-6 bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800" style="padding: 1.5rem; border-bottom: 1px solid #f3f4f6; background-color: #fff;">
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <textarea 
                    wire:model="content" 
                    rows="2" 
                    class="note-input"
                    placeholder="Escribe un comentario o nota interna aquí..."
                ></textarea>
                @error('content') <span style="font-size: 0.875rem; color: #ef4444; margin-top: -0.5rem;">{{ $message }}</span> @enderror
                
                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem;">
                    <select wire:model="type" class="note-select">
                        <option value="private">🔒 Nota privada</option>
                        <option value="customer">✉️ Mensaje al cliente</option>
                    </select>
                    
                    <button type="button" wire:click="addNote" class="note-btn">
                        Guardar Nota
                        <svg style="width: 1.125rem; height: 1.125rem; margin-left: 0.375rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </div>
                @error('type') <span style="font-size: 0.875rem; color: #ef4444; text-align: right;">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Lista de Notas (Línea de tiempo) -->
        <div style="padding: 1rem 2rem 2rem 1rem;">
            @foreach($notes as $note)
                <div class="note-timeline">
                    <div class="note-dot {{ $note->type }}"></div>
                    
                    <div class="note-card {{ $note->type }}">
                        @if($note->type === 'customer')
                            <div style="margin-bottom: 0.75rem;">
                                <span class="note-badge customer">Visible para el cliente</span>
                            </div>
                        @elseif($note->type === 'private')
                            <div style="margin-bottom: 0.75rem;">
                                <span class="note-badge private">Solo equipo interno</span>
                            </div>
                        @endif

                        <div style="font-size: 0.9375rem; color: #1f2937; line-height: 1.6; white-space: pre-wrap;">{{ $note->content }}</div>
                        
                        <div class="note-meta">
                            <svg style="width: 1rem; height: 1rem; color: #9ca3af;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>{{ $note->created_at->translatedFormat('j M Y, g:i a') }}</span>
                            
                            <span style="color: #d1d5db;">|</span>
                            
                            <svg style="width: 1rem; height: 1rem; color: #9ca3af;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span style="font-weight: 500; color: #4b5563;">
                                @if($note->user_id)
                                    {{ $note->user->name }} ({{ ucfirst($note->user->role) }})
                                @else
                                    Sistema Automático
                                @endif
                            </span>
                            
                            @if($note->type !== 'system')
                                <div style="flex-grow: 1; text-align: right;">
                                    <button wire:click="deleteNote({{ $note->id }})" class="note-delete">
                                        Eliminar
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
            
            @if($notes->isEmpty())
                <div style="padding: 3rem; text-align: center; color: #9ca3af;">
                    <svg style="width: 3rem; height: 3rem; margin: 0 auto 1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p style="font-size: 0.9375rem;">Aún no hay historial o notas en este pedido.</p>
                </div>
            @endif
        </div>
    </div>
</div>
