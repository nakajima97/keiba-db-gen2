<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

test('guests are redirected to the login page', function () {
    // Act
    $response = $this->get(route('dashboard'));

    // Assert
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    // Arrange
    $user = User::factory()->create();
    $this->actingAs($user);

    // Act
    $response = $this->get(route('dashboard'));

    // Assert
    $response->assertOk();
});

/**
 * テスト用の馬券購入データを作成するヘルパー
 *
 * @param array{
 *   user_id: int,
 *   race_date: string,
 *   amount: int,
 *   payout_amount: int|null,
 * } $params
 */
function createDashboardTicketPurchase(array $params): void
{
    $now = now();

    $venueId = DB::table('venues')->where('name', '東京')->value('id');
    if ($venueId === null) {
        $venueId = DB::table('venues')->insertGetId([
            'name' => '東京',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    $raceId = DB::table('races')
        ->where('venue_id', $venueId)
        ->where('race_date', $params['race_date'])
        ->where('race_number', 1)
        ->value('id');
    if ($raceId === null) {
        $raceId = DB::table('races')->insertGetId([
            'uid' => 'test-uid-'.uniqid(),
            'venue_id' => $venueId,
            'race_date' => $params['race_date'],
            'race_number' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    $ticketTypeId = DB::table('ticket_types')->where('name', 'tansho')->value('id');
    if ($ticketTypeId === null) {
        $ticketTypeId = DB::table('ticket_types')->insertGetId([
            'name' => 'tansho',
            'label' => '単勝',
            'sort_order' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    $buyTypeId = DB::table('buy_types')->where('name', 'single')->value('id');
    if ($buyTypeId === null) {
        $buyTypeId = DB::table('buy_types')->insertGetId([
            'name' => 'single',
            'label' => '通常',
            'sort_order' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    DB::table('ticket_purchases')->insert([
        'user_id' => $params['user_id'],
        'race_id' => $raceId,
        'ticket_type_id' => $ticketTypeId,
        'buy_type_id' => $buyTypeId,
        'selections' => json_encode(['horses' => [1]]),
        'amount' => $params['amount'],
        'payout_amount' => $params['payout_amount'],
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}

test('購入記録がない場合 summary=null、daily_balances=[]、available_years=[] が返る', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->get(route('dashboard', ['year' => 2026]));

    // Assert
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->where('summary', null)
        ->where('daily_balances', [])
        ->where('available_years', [])
        ->where('selected_year', 2026)
    );
});

test('購入記録がある場合 summary の各値が正しく計算されて返る', function () {
    // Arrange
    $user = User::factory()->create();
    createDashboardTicketPurchase([
        'user_id' => $user->id,
        'race_date' => '2026-04-05',
        'amount' => 1000,
        'payout_amount' => 1500,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('dashboard', ['year' => 2026]));

    // Assert
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->where('summary.total_purchase_amount', 1000)
        ->where('summary.total_payout_amount', 1500)
        ->where('summary.total_net_amount', 500)
        ->where('summary.total_return_rate', 150)
    );
});

test('日次データが日付ごとに正しく集計されて返る（同日複数馬券も合算）', function () {
    // Arrange
    $user = User::factory()->create();
    $now = now();

    $venueId = DB::table('venues')->insertGetId([
        'name' => '東京',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $raceId = DB::table('races')->insertGetId([
        'uid' => 'test-uid-'.uniqid(),
        'venue_id' => $venueId,
        'race_date' => '2026-04-05',
        'race_number' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $ticketTypeId = DB::table('ticket_types')->insertGetId([
        'name' => 'tansho',
        'label' => '単勝',
        'sort_order' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $buyTypeId = DB::table('buy_types')->insertGetId([
        'name' => 'single',
        'label' => '通常',
        'sort_order' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // 同一日に2件購入
    DB::table('ticket_purchases')->insert([
        [
            'user_id' => $user->id,
            'race_id' => $raceId,
            'ticket_type_id' => $ticketTypeId,
            'buy_type_id' => $buyTypeId,
            'selections' => json_encode(['horses' => [1]]),
            'amount' => 500,
            'payout_amount' => 1000,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'user_id' => $user->id,
            'race_id' => $raceId,
            'ticket_type_id' => $ticketTypeId,
            'buy_type_id' => $buyTypeId,
            'selections' => json_encode(['horses' => [2]]),
            'amount' => 300,
            'payout_amount' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ],
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('dashboard', ['year' => 2026]));

    // Assert
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->where('daily_balances.0.date', '2026-04-05')
        ->where('daily_balances.0.purchase_amount', 800)
        ->where('daily_balances.0.payout_amount', 1000)
        ->where('daily_balances.0.net_amount', 200)
    );
});

test('available_years に購入記録が存在する年がすべて含まれる', function () {
    // Arrange
    $user = User::factory()->create();
    createDashboardTicketPurchase([
        'user_id' => $user->id,
        'race_date' => '2025-12-01',
        'amount' => 100,
        'payout_amount' => 0,
    ]);
    createDashboardTicketPurchase([
        'user_id' => $user->id,
        'race_date' => '2026-04-05',
        'amount' => 100,
        'payout_amount' => 0,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('dashboard'));

    // Assert
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->has('available_years', 2)
        ->where('available_years', fn ($years) => $years->contains(2025) && $years->contains(2026))
    );
});

test('year クエリパラメータを指定した場合、その年のデータのみ返る', function () {
    // Arrange
    $user = User::factory()->create();
    createDashboardTicketPurchase([
        'user_id' => $user->id,
        'race_date' => '2025-12-01',
        'amount' => 500,
        'payout_amount' => 200,
    ]);
    createDashboardTicketPurchase([
        'user_id' => $user->id,
        'race_date' => '2026-04-05',
        'amount' => 1000,
        'payout_amount' => 1500,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('dashboard', ['year' => 2026]));

    // Assert
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->where('selected_year', 2026)
        ->where('summary.total_purchase_amount', 1000)
        ->where('summary.total_payout_amount', 1500)
    );
});

test('year を省略した場合は現在年のデータが返る', function () {
    // Arrange
    $user = User::factory()->create();
    $currentYear = now()->year;

    // Act
    $response = $this->actingAs($user)->get(route('dashboard'));

    // Assert
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->where('selected_year', $currentYear)
    );
});

test('指定年以外の馬券データは daily_balances に含まれない', function () {
    // Arrange
    $user = User::factory()->create();
    createDashboardTicketPurchase([
        'user_id' => $user->id,
        'race_date' => '2025-12-01',
        'amount' => 500,
        'payout_amount' => 200,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('dashboard', ['year' => 2026]));

    // Assert
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->where('daily_balances', [])
    );
});

test('payout_amount が null の馬券は 0 として集計される', function () {
    // Arrange
    $user = User::factory()->create();
    createDashboardTicketPurchase([
        'user_id' => $user->id,
        'race_date' => '2026-04-05',
        'amount' => 1000,
        'payout_amount' => null,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('dashboard', ['year' => 2026]));

    // Assert
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->where('daily_balances.0.purchase_amount', 1000)
        ->where('daily_balances.0.payout_amount', 0)
        ->where('daily_balances.0.net_amount', -1000)
    );
});

test('nagashi（流し）・2点の馬券: summary の total_purchase_amount が 単価×2 で返る', function () {
    // Arrange
    $user = User::factory()->create();
    $now = now();

    $venueId = DB::table('venues')->insertGetId([
        'name' => '東京',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $raceId = DB::table('races')->insertGetId([
        'uid' => 'test-uid-'.uniqid(),
        'venue_id' => $venueId,
        'race_date' => '2026-04-05',
        'race_number' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $ticketTypeId = DB::table('ticket_types')->insertGetId([
        'name' => 'umaren',
        'label' => '馬連',
        'sort_order' => 4,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $buyTypeId = DB::table('buy_types')->insertGetId([
        'name' => 'nagashi',
        'label' => '流し',
        'sort_order' => 2,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('ticket_purchases')->insert([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $ticketTypeId,
        'buy_type_id' => $buyTypeId,
        'selections' => json_encode(['axis' => [1], 'others' => [2, 3]]),
        'amount' => 200,
        'payout_amount' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('dashboard', ['year' => 2026]));

    // Assert
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->where('summary.total_purchase_amount', 400)
        ->where('daily_balances.0.purchase_amount', 400)
    );
});

test('amount が null の馬券は purchase_amount が 0 として集計される', function () {
    // Arrange
    $user = User::factory()->create();
    $now = now();

    $venueId = DB::table('venues')->insertGetId([
        'name' => '東京',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $raceId = DB::table('races')->insertGetId([
        'uid' => 'test-uid-'.uniqid(),
        'venue_id' => $venueId,
        'race_date' => '2026-04-05',
        'race_number' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $ticketTypeId = DB::table('ticket_types')->insertGetId([
        'name' => 'tansho',
        'label' => '単勝',
        'sort_order' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $buyTypeId = DB::table('buy_types')->insertGetId([
        'name' => 'single',
        'label' => '通常',
        'sort_order' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('ticket_purchases')->insert([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $ticketTypeId,
        'buy_type_id' => $buyTypeId,
        'selections' => json_encode(['horses' => [1]]),
        'amount' => null,
        'payout_amount' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('dashboard', ['year' => 2026]));

    // Assert
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->where('daily_balances.0.purchase_amount', 0)
        ->where('summary.total_purchase_amount', 0)
    );
});
