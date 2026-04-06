<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $venues = [
            ['name' => '東京', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '中山', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '阪神', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '京都', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '新潟', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '福島', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '小倉', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '函館', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '札幌', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '中京', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('venues')->insert($venues);
    }
}
