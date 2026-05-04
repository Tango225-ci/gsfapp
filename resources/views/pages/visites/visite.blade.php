@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Gestion des visites" />
    <div class="space-y-6">
        <x-common.component-card title="">
            <x-visites.visite-table />
        </x-common.component-card>

    </div>
@endsection
