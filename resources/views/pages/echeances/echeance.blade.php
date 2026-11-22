@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Gestion des Echeances" />
    <div class="space-y-6">
        <x-common.component-card title="Tableau Récapitulatif des Échéances">
            <x-echeances.echeance-table />
        </x-common.component-card>

    </div>
@endsection
