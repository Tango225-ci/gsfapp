@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Gestion des utilisateurs" />
    <div class="space-y-6">
        <x-common.component-card title="">
            <x-utilisateurs.utilisateur-table />
        </x-common.component-card>

    </div>
@endsection
