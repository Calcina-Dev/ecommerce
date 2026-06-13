<style>
    .pos-paginator {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 1rem;
        border-top: 1px solid var(--gray-200);
        margin-top: 1rem;
    }
    .dark .pos-paginator {
        border-color: var(--gray-800);
    }
    .pos-paginator-info {
        font-size: 0.875rem;
        color: var(--gray-600);
    }
    .dark .pos-paginator-info {
        color: var(--gray-400);
    }
    .pos-paginator-links {
        display: flex;
        gap: 0.25rem;
        align-items: center;
    }
    .pos-page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2rem;
        height: 2rem;
        padding: 0 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: 0.375rem;
        background: white;
        border: 1px solid var(--gray-300);
        color: var(--gray-700);
        cursor: pointer;
        transition: all 0.2s;
    }
    .dark .pos-page-btn {
        background: var(--gray-800);
        border-color: var(--gray-700);
        color: var(--gray-300);
    }
    .pos-page-btn:hover:not(:disabled) {
        background: var(--gray-50);
    }
    .dark .pos-page-btn:hover:not(:disabled) {
        background: var(--gray-700);
    }
    .pos-page-btn.active {
        background: var(--primary-600);
        border-color: var(--primary-600);
        color: white;
    }
    .dark .pos-page-btn.active {
        background: var(--primary-500);
        border-color: var(--primary-500);
    }
    .pos-page-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .pos-page-btn svg {
        width: 1rem;
        height: 1rem;
    }
</style>

@if ($paginator->hasPages())
    <div class="pos-paginator">
        <div class="pos-paginator-info">
            Mostrando <strong>{{ $paginator->firstItem() }}</strong> a <strong>{{ $paginator->lastItem() }}</strong> de <strong>{{ $paginator->total() }}</strong> resultados
        </div>
        
        <div class="pos-paginator-links">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <button class="pos-page-btn" disabled>
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" /></svg>
                </button>
            @else
                <button wire:click="previousPage" wire:loading.attr="disabled" class="pos-page-btn">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" /></svg>
                </button>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pos-page-btn" style="border:none; background:transparent;">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <button class="pos-page-btn active">{{ $page }}</button>
                        @else
                            <button wire:click="gotoPage({{ $page }})" class="pos-page-btn">{{ $page }}</button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" wire:loading.attr="disabled" class="pos-page-btn">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                </button>
            @else
                <button class="pos-page-btn" disabled>
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                </button>
            @endif
        </div>
    </div>
@endif
