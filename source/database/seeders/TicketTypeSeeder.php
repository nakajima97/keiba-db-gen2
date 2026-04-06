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
        $now = now();
        $types = [
            ['name' => 'tansho', 'label' => '単勝', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'fukusho', 'label' => '複勝', 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'wakuren', 'label' => '枠連', 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'umaren', 'label' => '馬連', 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'umatan', 'label' => '馬単', 'sort_order' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'wide', 'label' => 'ワイド', 'sort_order' => 6, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'sanrenpuku', 'label' => '三連複', 'sort_order' => 7, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'sanrentan', 'label' => '三連単', 'sort_order' => 8, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('ticket_types')->insert($types);
    }
}
