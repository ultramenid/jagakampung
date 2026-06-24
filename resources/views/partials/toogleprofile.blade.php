<div class="relative z-30" x-data="{ isProfileMenuOpen: false }">
    <button
      title="Profile"
      type="button"
      @click="isProfileMenuOpen = !isProfileMenuOpen"
      @click.away="isProfileMenuOpen = false"
      @keydown.escape="isProfileMenuOpen = false"
      aria-label="Akun"
      aria-haspopup="true"
      :aria-expanded="isProfileMenuOpen"
      class="inline-flex items-center justify-center min-w-9 min-h-9 cursor-pointer rounded-full ring-1 ring-white/15 hover:ring-white/30 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60"
    >
        @php $initial = mb_substr((string) session('name', 'A'), 0, 1) ?: 'A'; @endphp
        <span
          class="flex items-center justify-center w-9 h-9 rounded-full bg-white text-gray-900 font-mono text-xs font-semibold"
          aria-hidden="true"
        >{{ $initial }}</span>
    </button>

    <div
      x-show="isProfileMenuOpen"
      x-transition:enter="transition ease-out duration-150"
      x-transition:enter-start="opacity-0 -translate-y-1"
      x-transition:enter-end="opacity-100 translate-y-0"
      x-cloak
      role="menu"
      aria-label="Menu akun"
      @keydown.escape.window="isProfileMenuOpen = false"
      class="absolute right-0 w-56 mt-2 p-1 bg-white rounded-xl border border-gray-200 shadow-geist"
    >
      <div class="px-3 py-2">
        <p class="text-sm font-medium text-gray-900 truncate">{{ session('name') }}</p>
        <p class="font-mono text-[10px] uppercase tracking-widest text-gray-400 mt-0.5">
          {{ session('role_id') == 0 ? 'Administrator' : 'User' }}
        </p>
      </div>
      <div class="h-px bg-gray-100 my-1"></div>
      <a href="{{ url('/settings') }}" role="menuitem"
         class="flex items-center gap-2.5 w-full px-3 py-2 text-sm text-gray-600 rounded-md hover:bg-gray-50 hover:text-gray-900 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
          <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
          <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
        <span>Settings</span>
      </a>
      <form method="POST" action="{{ url('/cms/logout') }}" class="flex items-center gap-2.5 w-full px-3 py-2 text-sm text-red-600 rounded-md hover:bg-red-50 transition-colors">
        @csrf
        <button type="submit" role="menuitem" class="flex items-center gap-2.5 w-full text-left">
          <svg class="w-4 h-4" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
          </svg>
          <span>Log out</span>
        </button>
      </form>
    </div>
</div>
