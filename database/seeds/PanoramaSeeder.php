<?php

use App\Panorama;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PanoramaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();

        factory(Panorama::class, 6)
            ->create()
            ->each(function (Panorama $panorama) {
                $panorama
                    ->addMedia(__DIR__ . '/images/panorama_2.jpg')
                    ->preservingOriginal()
                    ->toMediaCollection(Panorama::COLLECTION_NAME);
            });

        DB::commit();
    }
}
