<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BuyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'single', 'label' => '通常', 'sort_order' => 1],
            ['name' => 'nagashi', 'label' => '流し', 'sort_order' => 2],
            ['name' => 'box', 'label' => 'ボックス', 'sort_order' => 3],
            ['name' => 'formation', 'label' => 'フォーメーション', 'sort_order' => 4],
        ];

        DB::table('buy_types')->insert($types);
    }
}
