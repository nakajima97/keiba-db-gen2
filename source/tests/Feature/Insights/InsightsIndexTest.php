<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

test('guests are redirected to the login page', function () {
    // Act
    $response = $this->get(route('insights'));

    // Assert
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the insights page', function () {
    // Arrange
    $user = User::factory()->create();
    $this->actingAs($user);

    // Act
    $response = $this->get(route('insights'));

    // Assert
    $response->assertOk();
});

test('period クエリパラメータが受け付けられる', function () {
    // Arrange
    $user = User::factory()->create();
    $this->actingAs($user);

    // Act
    $response = $this->get(route('insights', ['period' => '3m']));

    // Assert
    $response->assertOk();
});

test('Inertia レスポンスに必要なプロパティが含まれる', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->get(route('insights'));

    // Assert
    $response->assertInertia(fn ($page) => $page
        ->has('summary')
        ->has('patternBreakdown')
        ->has('popularityPatternMatrix')
        ->has('popularityReturns')
        ->has('monthlyTrends')
        ->has('recentSamples')
        ->has('period')
    );
});

test('period を省略した場合は 1m がデフォルトとして返る', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->get(route('insights'));

    // Assert
    $response->assertInertia(fn ($page) => $page
        ->where('period', '1m')
    );
});

/**
 * Insights テスト用の馬券購入データを作成するヘルパー。
 *
 * @param  array{
 *   user_id: int,
 *   race_date: string,
 *   buy_type: string,
 *   ticket_type: string,
 *   selections: array,
 *   unit_stake: int,
 *   payout_amount: int|null,
 *   with_race_result: bool,
 * }  $params
 */
function createInsightsTicket(array $params): void
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

    $raceId = DB::table('races')->insertGetId([
        'uid' => 'test-uid-'.uniqid(),
        'venue_id' => $venueId,
        'race_date' => $params['race_date'],
        'race_number' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $ticketTypeId = DB::table('ticket_types')->where('name', $params['ticket_type'])->value('id');
    if ($ticketTypeId === null) {
        $ticketTypeId = DB::table('ticket_types')->insertGetId([
            'name' => $params['ticket_type'],
            'label' => $params['ticket_type'],
            'sort_order' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    $buyTypeId = DB::table('buy_types')->where('name', $params['buy_type'])->value('id');
    if ($buyTypeId === null) {
        $buyTypeId = DB::table('buy_types')->insertGetId([
            'name' => $params['buy_type'],
            'label' => $params['buy_type'],
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
        'selections' => json_encode($params['selections']),
        'unit_stake' => $params['unit_stake'],
        'payout_amount' => $params['payout_amount'],
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    if ($params['with_race_result']) {
        DB::table('race_result_horses')->insert([
            'race_id' => $raceId,
            'finishing_order' => 1,
            'frame_number' => 1,
            'horse_number' => 1,
            'horse_name' => 'テストホース',
            'sex_age' => '牡3',
            'weight' => 56.0,
            'jockey_name' => 'テスト騎手',
            'race_time' => '1:23.4',
            'trainer_name' => 'テスト調教師',
            'popularity' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}

test('buy_type=nagashi かつ ticket_type=umaren 以外の馬券は集計対象に含まれない', function () {
    // Arrange
    $user = User::factory()->create();
    createInsightsTicket([
        'user_id' => $user->id,
        'race_date' => now()->subDays(3)->toDateString(),
        'buy_type' => 'single',
        'ticket_type' => 'tansho',
        'selections' => ['horses' => [1]],
        'unit_stake' => 1000,
        'payout_amount' => 1500,
        'with_race_result' => true,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('insights'));

    // Assert
    // summary は null（集計対象が存在しないため）
    $response->assertInertia(fn ($page) => $page
        ->where('summary', null)
    );
});

test('レース結果が未登録（race_result_horses が無い）の馬券は集計対象に含まれない', function () {
    // Arrange
    $user = User::factory()->create();
    createInsightsTicket([
        'user_id' => $user->id,
        'race_date' => now()->subDays(3)->toDateString(),
        'buy_type' => 'nagashi',
        'ticket_type' => 'umaren',
        'selections' => ['axis' => [1], 'others' => [2, 3]],
        'unit_stake' => 1000,
        'payout_amount' => 0,
        'with_race_result' => false,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('insights'));

    // Assert
    // summary は null（結果未登録の馬券は集計対象外）
    $response->assertInertia(fn ($page) => $page
        ->where('summary', null)
    );
});

test('period=1m のとき直近1ヶ月のデータのみ集計される', function () {
    // Arrange
    $user = User::factory()->create();
    // 2ヶ月前（含まれない想定）
    createInsightsTicket([
        'user_id' => $user->id,
        'race_date' => now()->subMonths(2)->toDateString(),
        'buy_type' => 'nagashi',
        'ticket_type' => 'umaren',
        'selections' => ['axis' => [1], 'others' => [2, 3]],
        'unit_stake' => 1000,
        'payout_amount' => 0,
        'with_race_result' => true,
    ]);
    // 直近（含まれる想定）
    createInsightsTicket([
        'user_id' => $user->id,
        'race_date' => now()->subDays(3)->toDateString(),
        'buy_type' => 'nagashi',
        'ticket_type' => 'umaren',
        'selections' => ['axis' => [1], 'others' => [2, 3]],
        'unit_stake' => 1000,
        'payout_amount' => 0,
        'with_race_result' => true,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('insights', ['period' => '1m']));

    // Assert
    $response->assertInertia(fn ($page) => $page
        ->where('summary.total_tickets', 1)
    );
});

test('period=all のとき期間制限なく全データが集計される', function () {
    // Arrange
    $user = User::factory()->create();
    // 2ヶ月前
    createInsightsTicket([
        'user_id' => $user->id,
        'race_date' => now()->subMonths(2)->toDateString(),
        'buy_type' => 'nagashi',
        'ticket_type' => 'umaren',
        'selections' => ['axis' => [1], 'others' => [2, 3]],
        'unit_stake' => 1000,
        'payout_amount' => 0,
        'with_race_result' => true,
    ]);
    // 直近
    createInsightsTicket([
        'user_id' => $user->id,
        'race_date' => now()->subDays(3)->toDateString(),
        'buy_type' => 'nagashi',
        'ticket_type' => 'umaren',
        'selections' => ['axis' => [1], 'others' => [2, 3]],
        'unit_stake' => 1000,
        'payout_amount' => 0,
        'with_race_result' => true,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('insights', ['period' => 'all']));

    // Assert
    $response->assertInertia(fn ($page) => $page
        ->where('summary.total_tickets', 2)
    );
});
