<div class="fixed inset-0 flex items-center justify-center bg-gray-50 px-4">
    <div class="w-full max-w-sm">
        <div class="flex flex-col items-center mb-8">
            <a href="/" class="text-2xl font-extrabold text-gray-900 tracking-tight mb-1">
                Jagakampung<span class="text-gray-400">.id</span>
            </a>
            <p class="text-xs text-gray-500">Masuk ke panel admin</p>
        </div>
        <div class="bg-white rounded border border-gray-100 shadow-lg p-10">
            @if (session()->has('error'))
                <div class="mb-4 px-3 py-2.5 rounded bg-red-50 border border-red-100 text-xs text-red-600">
                    {{ session('error') }}
                </div>
            @endif
            <form wire:submit.prevent="login">
                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-2">Email</label>
                        <input wire:model="email" type="email" autocomplete="email"
                               class="w-full border border-gray-200 rounded px-3 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 @error('email') border-red-300 @enderror"
                               placeholder="email">
                        @error('email') <p class="mt-1 text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-2">Password</label>
                        <input wire:model="password" type="password" autocomplete="current-password"
                               class="w-full border border-gray-200 rounded px-3 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 @error('password') border-red-300 @enderror"
                               placeholder="••••••••">
                        @error('password') <p class="mt-1 text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
                <button wire:loading.remove type="submit"
                        class="mt-8 w-full bg-gray-900 hover:bg-gray-700 text-white text-base font-semibold py-3 rounded transition cursor-pointer shadow-sm">
                    Masuk
                </button>
                <button wire:loading type="button"
                        class="mt-8 w-full bg-gray-700 text-white text-base font-semibold py-3 rounded cursor-not-allowed flex items-center justify-center gap-2 shadow-sm">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memproses...
                </button>
            </form>
        </div>
    </div>
</div>
