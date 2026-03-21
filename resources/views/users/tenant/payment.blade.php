@extends('layouts.app')

@section('header-title', 'PAYMENT RECORD')
@section('header-subtitle', 'Access payment history and document new receipts')

@section('content')

    @include('livewire.layouts.dashboard.admingreeting')
    <livewire:layouts.tenants.payment-history />


@endsection
