<div class="border-b border-gray-200 bg-white z-10">
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
            @endphp

            @foreach ($links as $link)
                <a href="{{ url($link['href']) }}"
                   class="relative flex-shrink-0 px-3 py-3.5 text-xs font-medium tracking-wide transition-colors
                          {{ $nav === $link['key']
                              ? 'text-gray-900 after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-gray-900 after:rounded-full'
                              : 'text-gray-400 hover:text-gray-700' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach

            @if (session('role_id') == 0)
                <a href="{{ url('/cms/users') }}"
                   class="relative flex-shrink-0 px-3 py-3.5 text-xs font-medium tracking-wide transition-colors
                          {{ $nav === 'users'
                              ? 'text-gray-900 after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-gray-900 after:rounded-full'
                              : 'text-gray-400 hover:text-gray-700' }}">
                    Users
                </a>
            @endif

            <a href="{{ url('/cms/settings') }}"
               class="relative flex-shrink-0 px-3 py-3.5 text-xs font-medium tracking-wide transition-colors
                      {{ $nav === 'settings'
                          ? 'text-gray-900 after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-gray-900 after:rounded-full'
                          : 'text-gray-400 hover:text-gray-700' }}">
                Settings
            </a>

        </nav>
    </div>
</div>
