@php
    $exts = ['pdf' => 'text-red-600 bg-red-50', 'doc' => 'text-accent-600 bg-accent-50', 'docx' => 'text-accent-600 bg-accent-50', 'xls' => 'text-green-700 bg-green-50', 'xlsx' => 'text-green-700 bg-green-50'];
@endphp

<div>
    <p class="font-mono text-[10px] font-medium uppercase tracking-wider text-gray-400 mb-3">Lampiran</p>

    @if ($lampirans && count($lampirans))
        <div class="space-y-2">
            @foreach ($lampirans as $index => $item)
                @php
                    $filename = $item['filename'] ?? (is_string($item['file']) ? basename($item['file']) : 'file');
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    $color = $exts[$ext] ?? 'text-gray-600 bg-gray-100';
                    $isEditing = $isEdit && $editIndex === $index;
                @endphp
                <div class="rounded-md border {{ $isEditing ? 'border-accent-300 bg-accent-50' : 'border-gray-200 hover:bg-gray-50' }} transition-colors group">
                    <div class="flex items-center gap-3 p-3">
                        <span class="font-mono text-[10px] font-medium uppercase px-1.5 py-0.5 rounded {{ $color }}">{{ $ext ?: 'file' }}</span>
                        <span class="flex-1 text-sm text-gray-700 truncate">{{ $item['nama'] }}</span>
                        @if (is_string($item['file']))
                            <a href="{{ Storage::url($item['file']) }}" target="_blank" download
                               class="opacity-0 group-hover:opacity-100 transition p-1 rounded-md hover:bg-gray-100" title="Download">
                                <svg class="w-3.5 h-3.5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10.75 2.75a.75.75 0 00-1.5 0v8.614L6.295 8.235a.75.75 0 10-1.09 1.03l4.25 4.5a.75.75 0 001.09 0l4.25-4.5a.75.75 0 00-1.09-1.03l-2.955 3.129V2.75z" />
                                    <path d="M3.5 12.75a.75.75 0 00-1.5 0v2.5A2.75 2.75 0 004.75 18h10.5A2.75 2.75 0 0018 15.25v-2.5a.75.75 0 00-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z" />
                                </svg>
                            </a>
                        @endif
                        <button wire:click="editPerkembangan({{ $index }})" type="button"
                                class="opacity-0 group-hover:opacity-100 transition p-1 rounded-md hover:bg-accent-100" title="Edit">
                            <svg class="w-3.5 h-3.5 text-accent-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.695 14.763l-1.262 3.154a.5.5 0 00.65.65l3.155-1.262a4 4 0 001.343-.885L17.5 5.5a2.121 2.121 0 00-3-3L3.58 13.42a4 4 0 00-.885 1.343z" />
                            </svg>
                        </button>
                        <button wire:click="delete({{ $index }})" type="button"
                                class="opacity-0 group-hover:opacity-100 transition p-1 rounded-md hover:bg-red-100" title="Hapus">
                            <svg class="w-3.5 h-3.5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    @if ($isEditing)
                        <div class="px-3 pb-3 space-y-2 border-t border-accent-200 pt-2">
                            <div>
                                <label class="gk-label">Nama lampiran</label>
                                <input wire:model="namalampiran" type="text" placeholder="Nama lampiran" class="gk-input h-9 text-xs">
                            </div>
                            <div>
                                <label class="gk-label">File baru (opsional)</label>
                                <input wire:model="filelampiran" type="file"
                                       class="w-full text-xs text-gray-600 border border-gray-200 rounded-md px-3 py-2 file:mr-3 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-medium file:bg-gray-100 file:text-gray-600 hover:file:bg-gray-200">
                            </div>
                            <div class="flex gap-2">
                                <button wire:click="updateLampiranTemp" type="button" class="gk-btn-accent gk-btn-sm flex-1">Simpan</button>
                                <button wire:click="resetForm" type="button" class="gk-btn-secondary gk-btn-sm">Batal</button>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-8 text-center">
            <svg class="w-8 h-8 text-gray-200 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
            </svg>
            <p class="text-sm text-gray-400">Belum ada lampiran</p>
        </div>
    @endif

    @if ($isAdd)
        <div class="mt-4 pt-4 border-t border-gray-100 space-y-2">
            <p class="font-mono text-[10px] font-medium uppercase tracking-wider text-gray-400">Tambah lampiran</p>
            <div>
                <label class="gk-label">Nama lampiran</label>
                <input wire:model="namalampiran" type="text" placeholder="Nama lampiran" class="gk-input h-9 text-xs">
                @error('namalampiran') <p class="gk-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="gk-label">File</label>
                <input wire:model="filelampiran" type="file"
                       class="w-full text-xs text-gray-600 border border-gray-200 rounded-md px-3 py-2 file:mr-3 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-medium file:bg-gray-100 file:text-gray-600 hover:file:bg-gray-200">
                @error('filelampiran') <p class="gk-error">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-2">
                <button wire:click="simpanLampiran" type="button" class="gk-btn-primary gk-btn-sm flex-1">Simpan</button>
                <button wire:click="resetForm" type="button" class="gk-btn-secondary gk-btn-sm">Batal</button>
            </div>
        </div>
    @endif

    @if (!$isAdd && !$isEdit)
        <button wire:click="addLampiran" type="button"
                class="mt-3 w-full flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-500 border border-dashed border-gray-300 rounded-md hover:border-gray-400 hover:text-gray-900 transition-colors">
            <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
            </svg>
            Tambah lampiran
        </button>
    @endif
</div>
