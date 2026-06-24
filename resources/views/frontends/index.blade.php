@extends('layouts.mapLayout')


@section('content')
    <div class="sm:flex hidden" x-data=" { legend: true }">
        {{-- @include('partials.legend') --}}
        <div class="w-2/12 h-full px-6">
            {{-- @include('partials.langSwitchPC') --}}
            <div class="w-full flex justify-start py-6 border-b border-gray-500">
                <a href="#" class="text-2xl font-semibold">Jagakampung</a>
            </div>
            {{-- <div class=" overflow-x-auto scrollbar-hide  justify-between px-4 flex gap-4  mt-6 border-b border-gray-300 z-30">
            <a class="whitespace-nowrap text-xs font-medium uppercase py-1 border-b-2 border-simontini">map</a>
            <a href="{{ route('index', app()->getLocale()) }}" class="whitespace-nowrap text-xs font-medium uppercase cursor-pointer py-1">home</a>
            <a href="#" class="whitespace-nowrap text-xs font-medium uppercase cursor-pointer py-1">about</a>
            <a href="#" class="whitespace-nowrap text-xs font-medium uppercase cursor-pointer py-1">insight</a>
            <a href="#" class="whitespace-nowrap text-xs font-medium uppercase cursor-pointer py-1">downloads</a>
        </div> --}}
            <div class="py-6 flex flex-col gap-1">
                <x-checkbox idAttr="adminkabkota" layerName="administrative_boundaries">
                    {{ __('Titik Konflik') }}
                </x-checkbox>
                <x-checkbox idAttr="kawasanhutan" layerName="konsesi">
                    {{ __('Kawasan Hutan') }}
                </x-checkbox>

                <x-checkbox idAttr="pbph" layerName="konsesi">
                    {{ __('Konsesi PBPH') }}
                </x-checkbox>
            </div>



        </div>
        
        <div id="map" class="w-10/12 h-screen "></div>

        {{-- Map Legend --}}
        <div
            class="fixed ml-[100px] bottom-8 left-1/2 -translate-x-1/2 flex z-[9999] items-center gap-3 bg-white/90 backdrop-blur-sm border border-gray-200 rounded-full px-5 py-2.5 shadow-md text-xs text-gray-600 select-none">

            <button id="toggleAktif" class="legend-btn flex items-center gap-1.5 cursor-pointer">
                <span class="w-3 h-3 rounded-full bg-[#890620] border-2 border-white shadow-sm inline-block"></span>
                Aktif
            </button>

            <span class="w-px h-3 bg-gray-200"></span>

            <button id="togglePotensi" class="legend-btn flex items-center gap-1.5 cursor-pointer">
                <span class="w-3 h-3 rounded-full bg-white border-[3px] border-[#348AA7] shadow-sm inline-block"></span>
                Potensi
            </button>

            <span class="w-px h-3 bg-gray-200"></span>

            {{-- <button id="toggleDraft" class="legend-btn flex items-center gap-1.5 cursor-pointer">
                <span class="w-3 h-3 rounded-full bg-white border-[3px] border-[#EAB308] shadow-sm inline-block"></span>
                Draft
            </button> --}}

        </div>

        {{-- Mobile backdrop --}}
        <div id="sidebarOverlay"
            class="fixed inset-0 bg-black/20 backdrop-blur-[1px] z-[9998] opacity-0 pointer-events-none transition-opacity duration-300"
            onclick="closeSidebar()">
        </div>

        {{-- Sidebar --}}
        <div id="sidebar" style="transform: translateX(100%);"
            class="fixed top-0 right-0 h-screen w-full sm:w-[480px] bg-white shadow-2xl z-[9999] transition-transform duration-300 ease-in-out flex flex-col">

            {{-- Sidebar Header --}}
            <div class="flex-shrink-0 px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-white">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-gray-900 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-sm text-gray-900 leading-tight">Detail Konflik</h2>
                        <p class="text-[10px] text-gray-400">Klik marker untuk melihat detail</p>
                    </div>
                </div>
                <button onclick="closeSidebar()"
                    class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-700 transition cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            {{-- Sidebar Body --}}
            <div id="sidebarContent" class="flex-1 overflow-y-auto">
                <div class="flex flex-col items-center justify-center h-full text-center px-8 pb-16">
                    <div
                        class="w-16 h-16 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-gray-300" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Pilih titik di peta</p>
                    <p class="text-xs text-gray-400 mt-1">Detail konflik akan muncul di sini</p>
                </div>
            </div>
        </div>

    </div>

    <div class="sm:hidden ">
        {{-- @include('partials.topbarMobile') --}}
        <div class="h-screen w-full flex items-center justify-center">
            <a class="text-center text-gray-500">under development for mobile version</a>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/map.js') }} "></script>
@endpush
