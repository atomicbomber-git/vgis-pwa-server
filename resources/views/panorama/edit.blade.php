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
            Edit Panorama
        </li>
    </ol>

    <div id="app">
        <h1> Edit Panorama </h1>

        <panorama-edit
            :map_config='{{ json_encode(config("map")) }}'
            :panoramas='{{ json_encode($panoramas) }}'
            :panorama='{{ json_encode($panorama) }}'
            submit_url='{{ route('panorama.update', $panorama)  }}'
            redirect_url='{{ route('panorama.edit', $panorama)  }}'
        >
        </panorama-edit>
    </div>
@endsection
