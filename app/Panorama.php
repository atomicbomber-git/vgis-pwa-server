<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Image;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Panorama extends Model implements HasMedia
{
    use InteractsWithMedia;

    public $table = "panorama";
    public $guarded = [];
    const MAX_ZOOM_LEVEL = 2;
    const COLLECTION_NAME = "panoramas";

    public function registerMediaConversions(Media $media = null): void
    {
        $image = Image::load($media->getPath());
        $max_zoom_level = static::MAX_ZOOM_LEVEL;

        $this->addMediaConversion("tile_0_0_0")
            ->optimize();

        for ($zoom_level = 1; $zoom_level <= $max_zoom_level; ++$zoom_level) {
            $n_tiles = pow(4, $zoom_level);

            $n_sqrt_tiles = sqrt($n_tiles);
            $tile_size_x = $image->getWidth() / $n_sqrt_tiles;
            $tile_size_y = $image->getHeight() / $n_sqrt_tiles;

            for ($i = 0; $i < $n_sqrt_tiles; ++$i) {
                for ($j = 0; $j < $n_sqrt_tiles; ++$j) {
                    $this->addMediaConversion("tile_${zoom_level}_${i}_${j}")
                        ->manualCrop($tile_size_x, $tile_size_y, $tile_size_x * $i, $tile_size_y * $j)
                        ->optimize();
                }
            }
        }
    }
}
