@extends("layouts.app")

@section("content")
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ \App\Providers\RouteServiceProvider::HOME }}">
                {{ config('app.name') }}
            </a>
        </li>
        <li class="breadcrumb-item active">
            Panorama List
        </li>
    </ol>

    <div id="app">
        <h1> Panorama List </h1>

        @include("layouts._messages")

        <div class="d-flex justify-content-end py-3">
            <a
                class="btn btn-primary"
                href="{{ route('panorama.create') }}">
                Create New Panorama
            </a>
        </div>

        <panorama-index
            :map_config='{{ json_encode(config("map")) }}'
            :panoramas='{{ json_encode($panoramas) }}'
        ></panorama-index>
    </div>
@endsection
