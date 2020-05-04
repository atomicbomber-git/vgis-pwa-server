<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Image;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property double latitude
 * @property double longitude
 */
class Panorama extends Model implements HasMedia
{
    use InteractsWithMedia;

    public $table = "panorama";
    public $guarded = [];
    const MAX_ZOOM_LEVEL = 1;
    const COLLECTION_NAME = "panoramas";

    public function panorama_links()
    {
        return $this->hasMany(PanoramaLink::class, "panorama_start_id");
    }

    public function calculateHeadingWith(Panorama $panorama_end)
    {
        $d_longitude = $panorama_end->longitude - $this->longitude;
        $y = sin($d_longitude) * cos($panorama_end->latitude);
        $x = cos($this->latitude) *
            sin($panorama_end->latitude) - sin($this->latitude) *
            cos($panorama_end->latitude)*cos($d_longitude);

        $heading = rad2deg(atan2($y, $x));
        return (360 - (($heading + 360) % 360));
    }

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
