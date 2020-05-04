<?php

namespace App\Http\Controllers;

use App\Panorama;
use Illuminate\Http\Request;

class PanoramaImageController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param Panorama $panorama
     * @param $zoom
     * @param $tile_x
     * @param $tile_y
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function __invoke(Request $request, Panorama $panorama, $zoom, $tile_x, $tile_y)
    {
        $original_image = $panorama->getFirstMedia(Panorama::COLLECTION_NAME);

        if ($original_image === null) {
            abort(404);
        }

        $image_path = null;
        try {
            $image_path =  $original_image->getPath("tile_${zoom}_${tile_x}_${tile_y}");
        }
        catch (\Exception $e) {
            abort(404);
        }

        return response()->file($image_path);
    }
}
