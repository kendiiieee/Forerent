@extends('layouts.app')

@section('header-title', 'DASHBOARD')
@section('header-subtitle', 'View a summary of all key property and account information')

@section('content')

    @include('livewire.layouts.dashboard.admingreeting')

    <livewire:layouts.dashboard.announcement-list :is-landlord="false" />
    <livewire:layouts.dashboard.calendar-widget />

    <livewire:layouts.tenants.tenant-dashboard-overview />

@endsection
