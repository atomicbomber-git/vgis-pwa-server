@extends("layouts.app")

@section("content")
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ \App\Providers\RouteServiceProvider::HOME  }}"> {{ config('app.name') }} </a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('panorama.index') }}">
                Panorama List
            </a>
        </li>
        <li class="breadcrumb-item active">
            Create New Panorama
        </li>
    </ol>

    <div id="app">
        <h1> Create New Panorama </h1>

        <panorama-create
            :map_config='{{ json_encode(config("map")) }}'
            :panoramas='{{ json_encode($panoramas) }}'
            submit_url='{{ route('panorama.store')  }}'
            redirect_url='{{ route('panorama.index')  }}'
        >
        </panorama-create>
    </div>
@endsection
