@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Gestion des Contrats" />
    <div class="space-y-6">
        <x-common.component-card title="">
            <x-contrats.contrat-table />
        </x-common.component-card>

    </div>
@endsection
