<?php

use App\Models\RacePayout;
use App\Models\RacePayoutHorse;
use App\Models\TicketPurchase;
use App\Models\User;
use App\UseCases\TicketPurchase\CalculatePayoutAmountAction;
use Illuminate\Support\Facades\DB;

/**
 * 会場 ID を取得（なければ作成）
 */
function getOrCreateVenueId(): int
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

    return (int) $venueId;
}

/**
 * レース ID を取得（なければ作成）
 */
function getOrCreateRaceId(int $venueId): int
{
    $now = now();
    $raceId = DB::table('races')->insertGetId([
        'uid' => 'test-uid-'.uniqid(),
        'venue_id' => $venueId,
        'race_date' => '2026-04-05',
        'race_number' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    return (int) $raceId;
}

/**
 * チケット種別 ID を取得（なければ作成）
 */
function getOrCreateTicketTypeId(string $name, string $label, int $sortOrder): int
{
    $now = now();
    $id = DB::table('ticket_types')->where('name', $name)->value('id');
    if ($id === null) {
        $id = DB::table('ticket_types')->insertGetId([
            'name' => $name,
            'label' => $label,
            'sort_order' => $sortOrder,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    return (int) $id;
}

/**
 * 購入方式 ID を取得（なければ作成）
 */
function getOrCreateBuyTypeId(string $name, string $label, int $sortOrder): int
{
    $now = now();
    $id = DB::table('buy_types')->where('name', $name)->value('id');
    if ($id === null) {
        $id = DB::table('buy_types')->insertGetId([
            'name' => $name,
            'label' => $label,
            'sort_order' => $sortOrder,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    return (int) $id;
}

/**
 * race_payouts と race_payout_horses を作成する
 *
 * @param  array<int, int>  $horseNumbers  sort_order 順に並んだ馬番
 */
function createRacePayout(int $raceId, int $ticketTypeId, int $payoutAmount, int $popularity, array $horseNumbers): void
{
    $payout = RacePayout::create([
        'race_id' => $raceId,
        'ticket_type_id' => $ticketTypeId,
        'payout_amount' => $payoutAmount,
        'popularity' => $popularity,
    ]);

    foreach ($horseNumbers as $index => $horseNumber) {
        RacePayoutHorse::create([
            'race_payout_id' => $payout->id,
            'horse_number' => $horseNumber,
            'sort_order' => $index + 1,
        ]);
    }
}

describe('CalculatePayoutAmountAction', function () {
    test('馬連ながし・一部的中: 軸11/相手{3,7,8}で 11-3 のみ的中なら払戻 1740 円', function () {
        // Arrange
        $user = User::factory()->create();
        $venueId = getOrCreateVenueId();
        $raceId = getOrCreateRaceId($venueId);
        $umarenId = getOrCreateTicketTypeId('umaren', '馬連', 4);
        $nagashiId = getOrCreateBuyTypeId('nagashi', '流し', 2);

        $purchase = TicketPurchase::create([
            'user_id' => $user->id,
            'race_id' => $raceId,
            'ticket_type_id' => $umarenId,
            'buy_type_id' => $nagashiId,
            'selections' => ['axis' => [11], 'others' => [3, 7, 8]],
            'unit_stake' => 100,
        ]);

        createRacePayout($raceId, $umarenId, 1740, 1, [3, 11]);

        // Act
        $result = app(CalculatePayoutAmountAction::class)->execute($purchase);

        // Assert
        expect($result)->toBe(1740);
    });

    test('ワイドながし・複数的中: 軸11/相手{3,7,8}で 11-3 と 11-7 が的中なら払戻 1300 円', function () {
        // Arrange
        $user = User::factory()->create();
        $venueId = getOrCreateVenueId();
        $raceId = getOrCreateRaceId($venueId);
        $wideId = getOrCreateTicketTypeId('wide', 'ワイド', 5);
        $nagashiId = getOrCreateBuyTypeId('nagashi', '流し', 2);

        $purchase = TicketPurchase::create([
            'user_id' => $user->id,
            'race_id' => $raceId,
            'ticket_type_id' => $wideId,
            'buy_type_id' => $nagashiId,
            'selections' => ['axis' => [11], 'others' => [3, 7, 8]],
            'unit_stake' => 100,
        ]);

        createRacePayout($raceId, $wideId, 580, 1, [3, 11]);
        createRacePayout($raceId, $wideId, 720, 2, [7, 11]);

        // Act
        $result = app(CalculatePayoutAmountAction::class)->execute($purchase);

        // Assert
        expect($result)->toBe(1300);
    });

    test('3連単ボックス・1的中: {1,2,3}のボックスで 1-2-3 が的中なら払戻 10000 円', function () {
        // Arrange
        $user = User::factory()->create();
        $venueId = getOrCreateVenueId();
        $raceId = getOrCreateRaceId($venueId);
        $sanrentanId = getOrCreateTicketTypeId('sanrentan', '3連単', 8);
        $boxId = getOrCreateBuyTypeId('box', 'ボックス', 3);

        $purchase = TicketPurchase::create([
            'user_id' => $user->id,
            'race_id' => $raceId,
            'ticket_type_id' => $sanrentanId,
            'buy_type_id' => $boxId,
            'selections' => ['horses' => [1, 2, 3]],
            'unit_stake' => 100,
        ]);

        createRacePayout($raceId, $sanrentanId, 10000, 1, [1, 2, 3]);

        // Act
        $result = app(CalculatePayoutAmountAction::class)->execute($purchase);

        // Assert
        expect($result)->toBe(10000);
    });

    test('単勝・単一組み合わせ: 3番が的中なら払戻 610 円', function () {
        // Arrange
        $user = User::factory()->create();
        $venueId = getOrCreateVenueId();
        $raceId = getOrCreateRaceId($venueId);
        $tanshoId = getOrCreateTicketTypeId('tansho', '単勝', 1);
        $singleId = getOrCreateBuyTypeId('single', '通常', 1);

        $purchase = TicketPurchase::create([
            'user_id' => $user->id,
            'race_id' => $raceId,
            'ticket_type_id' => $tanshoId,
            'buy_type_id' => $singleId,
            'selections' => ['horses' => [3]],
            'unit_stake' => 100,
        ]);

        createRacePayout($raceId, $tanshoId, 610, 1, [3]);

        // Act
        $result = app(CalculatePayoutAmountAction::class)->execute($purchase);

        // Assert
        expect($result)->toBe(610);
    });
});
