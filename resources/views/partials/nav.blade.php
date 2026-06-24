<div class="bg-[#0f0f0f] border-t border-white/5 z-10">
    <div class="max-w-3xl mx-auto sm:px-0 px-4">
        <nav class="flex items-center gap-1 overflow-x-auto scrollbar-hide">

            @php
                $links = [
                    ['key' => 'dashboard',  'href' => '/cms/dashboard',  'label' => 'Dashboard'],
                    ['key' => 'konflik',    'href' => '/cms/konflik',    'label' => 'Konflik'],
                    ['key' => 'grup',       'href' => '/cms/group',      'label' => 'Group'],
                    ['key' => 'perusahaan', 'href' => '/cms/perusahaan', 'label' => 'Perusahaan'],
                    ['key' => 'instansi',   'href' => '/cms/instansi',   'label' => 'Lembaga'],
                ];

                if (session('role_id') == 0) {
                    $links[] = ['key' => 'users', 'href' => '/cms/users', 'label' => 'Users'];
                }
                $links[] = ['key' => 'settings', 'href' => '#', 'label' => 'Settings'];

                $navBase = 'relative flex-shrink-0 px-3 py-3 font-mono text-[11px] uppercase tracking-widest transition-colors';
                $navActive = 'text-white after:absolute after:-bottom-px after:left-3 after:right-3 after:h-0.5 after:bg-[#b8324a]';
                $navIdle = 'text-gray-500 hover:text-gray-200 focus-visible:outline-none focus-visible:text-white focus-visible:ring-2 focus-visible:ring-[#b8324a]/40 rounded-sm';
            @endphp

            @foreach ($links as $link)
                <a href="{{ url($link['href']) }}"
                   class="{{ $navBase }} {{ $nav === $link['key'] ? $navActive : $navIdle }}">
                    {{ $link['label'] }}
                </a>
            @endforeach

        </nav>
    </div>
</div>
