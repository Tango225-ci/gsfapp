@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Gestion des Paiements" />
    <div class="space-y-6">
        <x-common.component-card title="">
            <x-paiements.paiement-table />
        </x-common.component-card>

    </div>
@endsection
