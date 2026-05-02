<?php

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * @return array{venueId: int, now: CarbonInterface}
 */
function createRaceResultMasterDataForDestroyTest(): array
{
    $now = now();

    DB::table('venues')->insert([
        'name' => '東京',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $venueId = DB::table('venues')->where('name', '東京')->value('id');

    $ticketTypes = [
        ['name' => 'tansho', 'label' => '単勝', 'sort_order' => 1],
        ['name' => 'fukusho', 'label' => '複勝', 'sort_order' => 2],
        ['name' => 'wakuren', 'label' => '枠連', 'sort_order' => 3],
        ['name' => 'umaren', 'label' => '馬連', 'sort_order' => 4],
        ['name' => 'umatan', 'label' => '馬単', 'sort_order' => 5],
        ['name' => 'wide', 'label' => 'ワイド', 'sort_order' => 6],
        ['name' => 'sanrenpuku', 'label' => '3連複', 'sort_order' => 7],
        ['name' => 'sanrentan', 'label' => '3連単', 'sort_order' => 8],
    ];

    foreach ($ticketTypes as $ticketType) {
        DB::table('ticket_types')->insert([
            'name' => $ticketType['name'],
            'label' => $ticketType['label'],
            'sort_order' => $ticketType['sort_order'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    return compact('venueId', 'now');
}

/**
 * @return array{raceId: int, raceUid: string}
 */
function createRaceWithUidForDestroyTest(int $venueId, CarbonInterface $now, int $raceNumber = 1): array
{
    $raceUid = 'test-uid-'.uniqid();
    $raceId = DB::table('races')->insertGetId([
        'uid' => $raceUid,
        'venue_id' => $venueId,
        'race_date' => '2026-04-05',
        'race_number' => $raceNumber,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    return compact('raceId', 'raceUid');
}

function createBuyTypesForDestroyTest(CarbonInterface $now): int
{
    $buyTypes = [
        ['name' => 'single', 'label' => '通常', 'sort_order' => 1],
        ['name' => 'nagashi', 'label' => '流し', 'sort_order' => 2],
        ['name' => 'box', 'label' => 'ボックス', 'sort_order' => 3],
        ['name' => 'formation', 'label' => 'フォーメーション', 'sort_order' => 4],
    ];

    foreach ($buyTypes as $buyType) {
        DB::table('buy_types')->insertOrIgnore([
            'name' => $buyType['name'],
            'label' => $buyType['label'],
            'sort_order' => $buyType['sort_order'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    return DB::table('buy_types')->where('name', 'single')->value('id');
}

/**
 * 指定レースに race_result_horses / race_payouts / race_payout_horses を作成する。
 */
function seedRaceResultForDestroyTest(int $raceId, CarbonInterface $now): void
{
    DB::table('race_result_horses')->insert([
        [
            'race_id' => $raceId,
            'finishing_order' => 1,
            'frame_number' => 2,
            'horse_number' => 3,
            'horse_name' => 'テスト馬A',
            'sex_age' => '牡3',
            'weight' => '57.0',
            'jockey_name' => '騎手A',
            'race_time' => '1:34.5',
            'trainer_name' => '調教師A',
            'popularity' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'race_id' => $raceId,
            'finishing_order' => 2,
            'frame_number' => 4,
            'horse_number' => 7,
            'horse_name' => 'テスト馬B',
            'sex_age' => '牝4',
            'weight' => '55.0',
            'jockey_name' => '騎手B',
            'race_time' => '1:34.8',
            'trainer_name' => '調教師B',
            'popularity' => 3,
            'created_at' => $now,
            'updated_at' => $now,
        ],
    ]);

    $tanshoTicketTypeId = DB::table('ticket_types')->where('name', 'tansho')->value('id');
    $payoutId = DB::table('race_payouts')->insertGetId([
        'race_id' => $raceId,
        'ticket_type_id' => $tanshoTicketTypeId,
        'payout_amount' => 610,
        'popularity' => 2,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('race_payout_horses')->insert([
        'race_payout_id' => $payoutId,
        'horse_number' => 3,
        'sort_order' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}

// ===== DELETE /races/{uid}/result =====

test('authenticated user can delete race result and related records are removed', function () {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterDataForDestroyTest();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUidForDestroyTest($venueId, $now);
    seedRaceResultForDestroyTest($raceId, $now);

    // Act
    $response = $this->actingAs($user)->deleteJson(route('races.result.destroy', ['uid' => $raceUid]));

    // Assert
    $response->assertOk();
    $response->assertJsonStructure(['message']);
    expect(DB::table('race_result_horses')->where('race_id', $raceId)->count())->toBe(0);
    expect(DB::table('race_payouts')->where('race_id', $raceId)->count())->toBe(0);
    expect(DB::table('race_payout_horses')->count())->toBe(0);
});

test('deleting race result resets ticket_purchases.payout_amount to null only for target race', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterDataForDestroyTest();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUidForDestroyTest($venueId, $now, 1);
    ['raceId' => $otherRaceId] = createRaceWithUidForDestroyTest($venueId, $now, 2);
    seedRaceResultForDestroyTest($raceId, $now);
    $singleBuyTypeId = createBuyTypesForDestroyTest($now);

    $tanshoTicketTypeId = DB::table('ticket_types')->where('name', 'tansho')->value('id');

    // 対象レースの馬券（payout_amount あり、削除でNULLにリセットされる）
    $targetPurchaseId = DB::table('ticket_purchases')->insertGetId([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $tanshoTicketTypeId,
        'buy_type_id' => $singleBuyTypeId,
        'selections' => json_encode(['horses' => [3]]),
        'unit_stake' => 100,
        'payout_amount' => 610,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // 対象レース・別ユーザーの馬券（同じくNULLにリセットされる：レース全体に対する操作）
    $otherUserPurchaseId = DB::table('ticket_purchases')->insertGetId([
        'user_id' => $otherUser->id,
        'race_id' => $raceId,
        'ticket_type_id' => $tanshoTicketTypeId,
        'buy_type_id' => $singleBuyTypeId,
        'selections' => json_encode(['horses' => [3]]),
        'unit_stake' => 100,
        'payout_amount' => 610,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // 別レースの馬券（影響を受けない）
    $otherRacePurchaseId = DB::table('ticket_purchases')->insertGetId([
        'user_id' => $user->id,
        'race_id' => $otherRaceId,
        'ticket_type_id' => $tanshoTicketTypeId,
        'buy_type_id' => $singleBuyTypeId,
        'selections' => json_encode(['horses' => [3]]),
        'unit_stake' => 100,
        'payout_amount' => 1000,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $this->actingAs($user)->deleteJson(route('races.result.destroy', ['uid' => $raceUid]));

    // Assert
    $this->assertDatabaseHas('ticket_purchases', [
        'id' => $targetPurchaseId,
        'payout_amount' => null,
    ]);
    $this->assertDatabaseHas('ticket_purchases', [
        'id' => $otherUserPurchaseId,
        'payout_amount' => null,
    ]);
    $this->assertDatabaseHas('ticket_purchases', [
        'id' => $otherRacePurchaseId,
        'payout_amount' => 1000,
    ]);
});

test('unauthenticated user is redirected to login page when deleting race result', function () {
    // Arrange
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterDataForDestroyTest();
    ['raceUid' => $raceUid] = createRaceWithUidForDestroyTest($venueId, $now);

    // Act
    $response = $this->delete(route('races.result.destroy', ['uid' => $raceUid]));

    // Assert
    $response->assertRedirect(route('login'));
});

test('deleting race result with non-existent uid returns 404', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->deleteJson(route('races.result.destroy', ['uid' => 'non-existent-uid']));

    // Assert
    $response->assertNotFound();
});

test('deleting race result returns 409 when no result exists for the race', function () {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterDataForDestroyTest();
    ['raceUid' => $raceUid] = createRaceWithUidForDestroyTest($venueId, $now);

    // Act
    $response = $this->actingAs($user)->deleteJson(route('races.result.destroy', ['uid' => $raceUid]));

    // Assert
    $response->assertStatus(409);
});
