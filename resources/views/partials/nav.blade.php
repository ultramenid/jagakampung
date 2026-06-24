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

                if (session('role_id') == 0) {
                    $links[] = ['key' => 'users', 'href' => '/cms/users', 'label' => 'Users'];
                }
                $links[] = ['key' => 'settings', 'href' => '#', 'label' => 'Settings'];

                $navBase = 'relative flex-shrink-0 px-3 py-3.5 text-sm font-medium transition-colors';
                $navActive = 'text-gray-900 after:absolute after:-bottom-px after:left-0 after:right-0 after:h-0.5 after:bg-gray-900';
                $navIdle = 'text-gray-400 hover:text-gray-900';
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
