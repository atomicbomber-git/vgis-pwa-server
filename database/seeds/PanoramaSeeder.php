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

        $panoramas = factory(Panorama::class, 6)
            ->create()
            ->each(function (Panorama $panorama) {
                $panorama
                    ->addMedia(__DIR__ . '/images/panorama_2.jpg')
                    ->preservingOriginal()
                    ->toMediaCollection(Panorama::COLLECTION_NAME);
            });

        $panoramas->first()->update([
            "is_first" => true,
        ]);

        DB::commit();
    }
}
