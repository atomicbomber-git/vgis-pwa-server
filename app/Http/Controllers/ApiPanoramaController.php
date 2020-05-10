<?php

namespace App\Http\Controllers;

use App\Panorama;
use Illuminate\Http\Request;

class ApiPanoramaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Http\Response
     */
    public function index()
    {
        return Panorama::query()
            ->with("panorama_links.end")
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
    }
}
