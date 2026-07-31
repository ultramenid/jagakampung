@extends('layouts.dashboardLayout')


@section('content')
{{-- ponytail: kolom flex setinggi viewport supaya peta mengisi sisa ruang yang
     benar-benar tersedia. Sebelumnya peta pakai h-[89vh] di aliran normal DI BAWAH
     header+nav (~102px), jadi total tinggi halaman = 102px + 89vh dan meluber di
     viewport mana pun yang lebih pendek dari ~930px CSS — termasuk browser zoom
     125% pada layar 1440×900 (= 1152×720 CSS). Scroll yang muncul itulah yang
     membuat kontrol `fixed bottom-8` tidak lagi sejajar dengan tepi peta. --}}
<div class="h-dvh flex flex-col overflow-hidden">
    <div class="bg-white shadow-sm flex-shrink-0">
        @include('partials.header')
        @include('partials.nav')
    </div>

    <livewire:cms-konflik />
</div>
@endsection
