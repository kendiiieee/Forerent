@extends('layouts.app')

@section('header-title', 'MESSENGER')
@section('header-subtitle', 'View and send messages')

@section('content')


    <div class="h-[calc(100vh-140px)] min-h-[600px] w-full">
        <livewire:layouts.message.message-system />
    </div>
@endsection
