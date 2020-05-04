<?php

namespace App\Http\Controllers;

use App\Panorama;
use App\PanoramaLink;
use Illuminate\Http\Request;

class PanoramaLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $panoramas = Panorama::query()->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
     * @param  \App\PanoramaLink  $panoramaLink
     * @return \Illuminate\Http\Response
     */
    public function show(PanoramaLink $panoramaLink)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PanoramaLink  $panoramaLink
     * @return \Illuminate\Http\Response
     */
    public function edit(PanoramaLink $panoramaLink)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PanoramaLink  $panoramaLink
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PanoramaLink $panoramaLink)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PanoramaLink  $panoramaLink
     * @return \Illuminate\Http\Response
     */
    public function destroy(PanoramaLink $panoramaLink)
    {
        //
    }
}
