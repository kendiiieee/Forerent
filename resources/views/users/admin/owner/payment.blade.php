@extends('layouts.app')

@section('header-title', 'PAYMENT RECORD')
@section('header-subtitle', 'Rental payment record')

@section('content')

    @include('livewire.layouts.dashboard.admingreeting')

    <livewire:layouts.financials.payment-receipts />

@endsection
