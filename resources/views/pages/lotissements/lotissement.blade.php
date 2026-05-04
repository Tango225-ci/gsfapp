@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Gestion des Lotissements" />
    <div class="space-y-6">
        <x-common.component-card title="">
            <x-lotissements.lotissement-table />
        </x-common.component-card>

    </div>
@endsection
