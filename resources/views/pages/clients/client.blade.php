@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Gestion des Clients" />
    <div class="space-y-6">
        <x-common.component-card title="">
            <x-clients.client-table />
        </x-common.component-card>

    </div>
@endsection
