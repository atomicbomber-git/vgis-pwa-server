<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PanoramaLink extends Model
{
    protected $table = "panorama_link";

    public function end(): BelongsTo
    {
        return $this->belongsTo(Panorama::class, "panorama_end_id");
    }
}
