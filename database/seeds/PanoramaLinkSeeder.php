<?php

use App\Panorama;
use App\PanoramaLink;
use Illuminate\Database\Seeder;

class PanoramaLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $panoramas = Panorama::query()->get();

        foreach ($panoramas as $panorama_start) {
            foreach ($panoramas as $panorama_end) {
                if ($panorama_start->id === $panorama_end->id) {
                    continue;
                }

                PanoramaLink::query()->create([
                    "panorama_start_id" => $panorama_start->id,
                    "panorama_end_id" => $panorama_end->id,
                    "heading" => $panorama_start->calculateHeadingWith($panorama_end),
                ]);
            }
        }
    }
}
