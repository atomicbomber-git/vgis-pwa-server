<?php

namespace App\Http\Controllers;

use App\Panorama;
use Illuminate\Http\Request;

class PanoramaOriginalImageController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function __invoke(Request $request, Panorama $panorama)
    {
        return response()->file($panorama->getFirstMediaPath(Panorama::COLLECTION_NAME));
    }
}
