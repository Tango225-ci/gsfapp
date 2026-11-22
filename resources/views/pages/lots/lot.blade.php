@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Gestion des Lots" />
    <div class="space-y-6">
        <x-common.component-card title="Tableau Récapitulatif des Lots">
            <x-lots.lot-table />
        </x-common.component-card>

    </div>
@endsection
