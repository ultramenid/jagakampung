@if ($paginator->hasPages())
<nav class="flex items-center justify-between mt-6">
    <p class="text-xs text-gray-500">
        Menampilkan <span class="gk-mono">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
        dari <span class="gk-mono">{{ $paginator->total() }}</span> data
    </p>

    <div class="flex items-center gap-1">
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-md text-gray-300 border border-gray-100 cursor-not-allowed">&lsaquo;</span>
        @else
            <button wire:click="previousPage" wire:loading.attr="disabled" class="gk-btn-secondary gk-btn-sm w-8 px-0">&lsaquo;</button>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 text-xs text-gray-300">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-md gk-mono text-xs font-medium text-white bg-gray-900">{{ $page }}</span>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="gk-btn-secondary gk-btn-sm w-8 px-0 gk-mono">{{ $page }}</button>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <button wire:click="nextPage" wire:loading.attr="disabled" class="gk-btn-secondary gk-btn-sm w-8 px-0">&rsaquo;</button>
        @else
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-md text-gray-300 border border-gray-100 cursor-not-allowed">&rsaquo;</span>
        @endif
    </div>
</nav>
@endif
