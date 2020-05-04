<?php

namespace App\Http\Controllers;

use App\Constants\MessageState;
use App\Panorama;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PanoramaController extends Controller
{
    public function __construct()
    {
        $this->middleware([
           "auth",
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $panoramas = Panorama::query()
            ->get();

        return response()->view("panorama.index", compact(
            "panoramas"
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $panoramas = Panorama::query()
            ->get();

        return response()->view("panorama.create", compact(
            "panoramas"
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = collect($request->validate([
           "nama" => ["required", "string"],
           "deskripsi" => ["required", "string"],
           "latitude" => ["required", "numeric"],
           "longitude" => ["required", "numeric"],
           "image" => ["required", "image"],
        ]));

        /** @var Panorama $panorama */
        $panorama = Panorama::query()->create($data->except(["image"])->toArray());
        $panorama->addMediaFromRequest("image")
            ->toMediaCollection(Panorama::COLLECTION_NAME);

        session()->flash("messages", [
            [
                "state" => MessageState::STATE_SUCCESS,
                "content" => __("messages.update.success")
            ]
        ]);

        return response('Success', 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Panorama  $panorama
     * @return \Illuminate\Http\Response
     */
    public function show(Panorama $panorama)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Panorama  $panorama
     * @return \Illuminate\Http\Response
     */
    public function edit(Panorama $panorama)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Panorama  $panorama
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Panorama $panorama)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Panorama  $panorama
     * @return \Illuminate\Http\Response
     */
    public function destroy(Panorama $panorama)
    {
        DB::beginTransaction();

        $panorama->getMedia(Panorama::COLLECTION_NAME)
            ->each(function (Media $media) {
                $media->forceDelete();
            });
        $panorama->forceDelete();

        DB::commit();

        session()->flash("messages", [
            [
                "state" => MessageState::STATE_SUCCESS,
                "content" => __("messages.update.success")
            ]
        ]);

        return new Response('success', 200);
    }
}
