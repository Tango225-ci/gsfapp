@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Gestion des Documents" />
    <div class="space-y-6">
        <x-common.component-card title="">
            <x-documents.document-table />
        </x-common.component-card>

    </div>
@endsection
