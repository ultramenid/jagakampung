<div>
    @if ($deleter)
    <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4">

        <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" @click="open = false"></div>

        <div class="relative bg-white rounded-xl shadow-geist w-full max-w-sm p-6 z-10">
            <div class="flex flex-col items-center text-center gap-3">
                <div class="w-11 h-11 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Hapus data</h3>
                    <p class="text-xs text-gray-500 mt-1">Hapus <span class="font-medium text-gray-900">{{ $deleteName }}</span>? Tindakan ini tidak dapat dibatalkan.</p>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button wire:loading.remove wire:target="closeDelete" wire:click='closeDelete' type="button" class="gk-btn-secondary gk-btn-sm flex-1">Batal</button>
                <button wire:loading wire:target="closeDelete" type="button" class="gk-btn-secondary gk-btn-sm flex-1" disabled>Batal</button>

                <button wire:loading.remove wire:target="deleting({{ $deleteID }})" wire:click="deleting({{ $deleteID }})" type="button"
                        class="gk-btn gk-btn-sm flex-1 bg-red-600 text-white hover:bg-red-700">Hapus</button>
                <button wire:loading wire:target="deleting({{ $deleteID }})" type="button" class="gk-btn gk-btn-sm flex-1 bg-red-600 text-white" disabled>
                    <svg class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Menghapus…
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
