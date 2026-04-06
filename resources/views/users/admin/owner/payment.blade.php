@extends('layouts.app')

@section('header-title', 'PAYMENT DOCUMENTATION')
@section('header-subtitle', 'Rental payment documentation')

@section('content')

    @include('livewire.layouts.dashboard.admingreeting')

    <livewire:layouts.financials.payment-receipts />

@endsection
