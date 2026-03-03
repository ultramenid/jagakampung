@extends('layouts.mapLayout')


@section('content')
    <div class="sm:flex hidden" x-data=" {legend:true}">
    {{-- @include('partials.legend') --}}
    <div class="w-2/12 h-full px-6" >
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
                        {{__('Titik Konflik')}}
            </x-checkbox>
            <x-checkbox idAttr="kawasanhutan" layerName="konsesi">
                        {{__('Kawasan Hutan')}}
            </x-checkbox>

            <x-checkbox idAttr="konsesi" layerName="konsesi">
                        {{__('Konsesi PBPH')}}
            </x-checkbox>
        </div>



    </div>
    <div id="map" class="w-10/12 h-screen "></div>

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
