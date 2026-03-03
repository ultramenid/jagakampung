@extends('layouts.dashboardLayout')


@section('content')
<div class="bg-white shadow-sm">
    @include('partials.header')
    @include('partials.nav')
</div>
    <x-toaster-hub />

    <livewire:tambah-konflik />

@endsection
