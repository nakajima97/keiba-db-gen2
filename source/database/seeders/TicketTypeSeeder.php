<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'tansho', 'label' => '単勝', 'sort_order' => 1],
            ['name' => 'fukusho', 'label' => '複勝', 'sort_order' => 2],
            ['name' => 'wakuren', 'label' => '枠連', 'sort_order' => 3],
            ['name' => 'umaren', 'label' => '馬連', 'sort_order' => 4],
            ['name' => 'umatan', 'label' => '馬単', 'sort_order' => 5],
            ['name' => 'wide', 'label' => 'ワイド', 'sort_order' => 6],
            ['name' => 'sanrenpuku', 'label' => '三連複', 'sort_order' => 7],
            ['name' => 'sanrentan', 'label' => '三連単', 'sort_order' => 8],
        ];

        DB::table('ticket_types')->insert($types);
    }
}
