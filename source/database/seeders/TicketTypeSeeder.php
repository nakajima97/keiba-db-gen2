<?php

namespace Database\Seeders;

use App\Enums\TicketTypeName;
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
            ['name' => TicketTypeName::Tansho->value, 'label' => '単勝', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => TicketTypeName::Fukusho->value, 'label' => '複勝', 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['name' => TicketTypeName::Wakuren->value, 'label' => '枠連', 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['name' => TicketTypeName::Umaren->value, 'label' => '馬連', 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['name' => TicketTypeName::Umatan->value, 'label' => '馬単', 'sort_order' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['name' => TicketTypeName::Wide->value, 'label' => 'ワイド', 'sort_order' => 6, 'created_at' => $now, 'updated_at' => $now],
            ['name' => TicketTypeName::Sanrenpuku->value, 'label' => '三連複', 'sort_order' => 7, 'created_at' => $now, 'updated_at' => $now],
            ['name' => TicketTypeName::Sanrentan->value, 'label' => '三連単', 'sort_order' => 8, 'created_at' => $now, 'updated_at' => $now],
            ['name' => TicketTypeName::Tanpuku->value, 'label' => '単複', 'sort_order' => 9, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('ticket_types')->insert($types);
    }
}
