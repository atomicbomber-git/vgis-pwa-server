<?php

namespace App\Http\Controllers;

use App\Panorama;
use App\PanoramaLink;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PanoramaLinkController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            "panorama_start_id" => ["required", "exists:panorama,id"],
            "panorama_end_id" => ["required", "exists:panorama,id"],
        ]);

        /** @var Panorama $panorama_start */
        $panorama_start = Panorama::query()->find($data["panorama_start_id"]);

        /** @var Panorama $panorama_end */
        $panorama_end = Panorama::query()->find($data["panorama_end_id"]);

        DB::beginTransaction();

        $start_link = PanoramaLink::query()->updateOrCreate([
            "panorama_start_id" => $data["panorama_start_id"],
            "panorama_end_id" => $data["panorama_end_id"],
        ], [
            "heading" => $panorama_start->calculateHeadingWith($panorama_end)
        ]);

        $end_link = PanoramaLink::query()->updateOrCreate([
            "panorama_start_id" => $data["panorama_end_id"],
            "panorama_end_id" => $data["panorama_start_id"],
        ], [
            "heading" => $panorama_end->calculateHeadingWith($panorama_start)
        ]);

        DB::commit();

        return new Response([
            "start_link" => $start_link->load("end"),
            "end_link" => $end_link->load("end"),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\PanoramaLink  $panorama_link
     * @return \Illuminate\Http\Response
     */
    public function show(PanoramaLink $panorama_link)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PanoramaLink  $panorama_link
     * @return \Illuminate\Http\Response
     */
    public function edit(PanoramaLink $panorama_link)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PanoramaLink  $panorama_link
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PanoramaLink $panorama_link)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PanoramaLink  $panorama_link
     * @return \Illuminate\Http\Response
     */
    public function destroy(PanoramaLink $panorama_link)
    {
        DB::beginTransaction();

        PanoramaLink::query()
            ->where([
                "panorama_end_id" => $panorama_link->panorama_start_id,
                "panorama_start_id" => $panorama_link->panorama_end_id,
            ])
            ->forceDelete();

        $panorama_link->forceDelete();

        DB::commit();

        return new Response('success');
    }
}
