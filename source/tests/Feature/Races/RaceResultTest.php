<?php

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

$sampleText = implode("\n", [
    "単勝\t3\t610円\t2番人気",
    "複勝\t3\t170円\t2番人気",
    "\t6\t110円\t1番人気",
    "\t11\t170円\t3番人気",
    "枠連\t2-4\t680円\t2番人気",
    "ワイド\t3-6\t330円\t1番人気",
    "\t3-11\t710円\t8番人気",
    "\t6-11\t340円\t2番人気",
    "馬連\t3-6\t700円\t1番人気",
    "馬単\t3-6\t1,730円\t4番人気",
    "3連複\t3-6-11\t1,550円\t2番人気",
    "3連単\t3-6-11\t8,820円\t16番人気",
]);

// JRA公式サイトからコピーしたフォーマット（券種名が別行のヘッダー）
$jraSampleText = implode("\n", [
    "単勝\t",
    "3\t610円\t2番人気",
    "複勝\t",
    "3\t170円\t2番人気",
    "6\t110円\t1番人気",
    "11\t170円\t3番人気",
    "枠連\t",
    "2-4\t680円\t2番人気",
    "ワイド\t",
    "3-6\t330円\t1番人気",
    "3-11\t710円\t8番人気",
    "6-11\t340円\t2番人気",
    "馬連\t",
    "3-6\t700円\t1番人気",
    "馬単\t",
    "3-6\t1,730円\t4番人気",
    "3連複\t",
    "3-6-11\t1,550円\t2番人気",
    "3連単\t",
    "3-6-11\t8,820円\t16番人気",
]);

/**
 * @return array{venueId: int, now: CarbonInterface}
 */
function createRaceResultMasterData(): array
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
function createRaceWithUid(int $venueId, CarbonInterface $now): array
{
    $raceUid = 'test-uid-'.uniqid();
    $raceId = DB::table('races')->insertGetId([
        'uid' => $raceUid,
        'venue_id' => $venueId,
        'race_date' => '2026-04-05',
        'race_number' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    return compact('raceId', 'raceUid');
}

// ===== GET /races/{uid}/result/new =====

test('authenticated user can access race result create page', function () {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceUid' => $raceUid] = createRaceWithUid($venueId, $now);

    // Act
    $response = $this->actingAs($user)->get(route('races.result.create', ['uid' => $raceUid]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page->component('races/result/create'));
});

test('unauthenticated user is redirected to login page when accessing race result create page', function () {
    // Arrange
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceUid' => $raceUid] = createRaceWithUid($venueId, $now);

    // Act
    $response = $this->get(route('races.result.create', ['uid' => $raceUid]));

    // Assert
    $response->assertRedirect(route('login'));
});

test('accessing race result create page with non-existent uid returns 404', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->get(route('races.result.create', ['uid' => 'non-existent-uid']));

    // Assert
    $response->assertNotFound();
});

// ===== POST /races/{uid}/result =====

test('valid payout text is stored with 8 race_payouts records', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUid($venueId, $now);

    // Act
    $response = $this->actingAs($user)->post(route('races.result.store', ['uid' => $raceUid]), [
        'text' => $sampleText,
    ]);

    // Assert
    $response->assertRedirect(route('tickets.index'));
    expect(DB::table('race_payouts')->where('race_id', $raceId)->count())->toBe(12);
});

test('tansho and fukusho horse_number is stored correctly in race_payout_horses', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUid($venueId, $now);

    // Act
    $this->actingAs($user)->post(route('races.result.store', ['uid' => $raceUid]), [
        'text' => $sampleText,
    ]);

    // Assert
    $tanshoTicketTypeId = DB::table('ticket_types')->where('name', 'tansho')->value('id');
    $tanshoPayoutId = DB::table('race_payouts')
        ->where('race_id', $raceId)
        ->where('ticket_type_id', $tanshoTicketTypeId)
        ->value('id');

    $this->assertDatabaseHas('race_payout_horses', [
        'race_payout_id' => $tanshoPayoutId,
        'horse_number' => 3,
        'sort_order' => 1,
    ]);
});

test('umatan and sanrentan horse_numbers are stored with correct sort_order', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUid($venueId, $now);

    // Act
    $this->actingAs($user)->post(route('races.result.store', ['uid' => $raceUid]), [
        'text' => $sampleText,
    ]);

    // Assert
    $umatanTicketTypeId = DB::table('ticket_types')->where('name', 'umatan')->value('id');
    $umatanPayoutId = DB::table('race_payouts')
        ->where('race_id', $raceId)
        ->where('ticket_type_id', $umatanTicketTypeId)
        ->value('id');

    $this->assertDatabaseHas('race_payout_horses', [
        'race_payout_id' => $umatanPayoutId,
        'horse_number' => 3,
        'sort_order' => 1,
    ]);
    $this->assertDatabaseHas('race_payout_horses', [
        'race_payout_id' => $umatanPayoutId,
        'horse_number' => 6,
        'sort_order' => 2,
    ]);

    $sanrentanTicketTypeId = DB::table('ticket_types')->where('name', 'sanrentan')->value('id');
    $sanrentanPayoutId = DB::table('race_payouts')
        ->where('race_id', $raceId)
        ->where('ticket_type_id', $sanrentanTicketTypeId)
        ->value('id');

    $this->assertDatabaseHas('race_payout_horses', [
        'race_payout_id' => $sanrentanPayoutId,
        'horse_number' => 3,
        'sort_order' => 1,
    ]);
    $this->assertDatabaseHas('race_payout_horses', [
        'race_payout_id' => $sanrentanPayoutId,
        'horse_number' => 6,
        'sort_order' => 2,
    ]);
    $this->assertDatabaseHas('race_payout_horses', [
        'race_payout_id' => $sanrentanPayoutId,
        'horse_number' => 11,
        'sort_order' => 3,
    ]);
});

test('umaren, wide, wakuren, and sanrenpuku horse_numbers are stored in ascending order', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUid($venueId, $now);

    // Act
    $this->actingAs($user)->post(route('races.result.store', ['uid' => $raceUid]), [
        'text' => $sampleText,
    ]);

    // Assert
    $umarenTicketTypeId = DB::table('ticket_types')->where('name', 'umaren')->value('id');
    $umarenPayoutId = DB::table('race_payouts')
        ->where('race_id', $raceId)
        ->where('ticket_type_id', $umarenTicketTypeId)
        ->value('id');

    $umarenHorses = DB::table('race_payout_horses')
        ->where('race_payout_id', $umarenPayoutId)
        ->orderBy('sort_order')
        ->pluck('horse_number')
        ->all();
    expect($umarenHorses)->toBe([3, 6]);

    $sanrenpukuTicketTypeId = DB::table('ticket_types')->where('name', 'sanrenpuku')->value('id');
    $sanrenpukuPayoutId = DB::table('race_payouts')
        ->where('race_id', $raceId)
        ->where('ticket_type_id', $sanrenpukuTicketTypeId)
        ->value('id');

    $sanrenpukuHorses = DB::table('race_payout_horses')
        ->where('race_payout_id', $sanrenpukuPayoutId)
        ->orderBy('sort_order')
        ->pluck('horse_number')
        ->all();
    expect($sanrenpukuHorses)->toBe([3, 6, 11]);
});

test('successful post redirects to race result edit page', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceUid' => $raceUid] = createRaceWithUid($venueId, $now);

    // Act
    $response = $this->actingAs($user)->post(route('races.result.store', ['uid' => $raceUid]), [
        'text' => $sampleText,
    ]);

    // Assert
    $response->assertRedirect(route('tickets.index'));
});

test('empty text returns validation error and nothing is stored', function () {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUid($venueId, $now);

    // Act
    $response = $this->actingAs($user)->post(route('races.result.store', ['uid' => $raceUid]), [
        'text' => '',
    ]);

    // Assert
    $response->assertSessionHasErrors(['text']);
    expect(DB::table('race_payouts')->where('race_id', $raceId)->count())->toBe(0);
});

test('invalid format text returns error and nothing is stored', function () {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUid($venueId, $now);

    // Act
    $response = $this->actingAs($user)->post(route('races.result.store', ['uid' => $raceUid]), [
        'text' => 'invalid format text',
    ]);

    // Assert
    $response->assertSessionHasErrors();
    expect(DB::table('race_payouts')->where('race_id', $raceId)->count())->toBe(0);
});

test('missing ticket types in text returns error and nothing is stored', function () {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUid($venueId, $now);

    $incompleteText = implode("\n", [
        "単勝\t3\t610円\t2番人気",
        "複勝\t3\t170円\t2番人気",
        "\t6\t110円\t1番人気",
        "\t11\t170円\t3番人気",
    ]);

    // Act
    $response = $this->actingAs($user)->post(route('races.result.store', ['uid' => $raceUid]), [
        'text' => $incompleteText,
    ]);

    // Assert
    $response->assertSessionHasErrors();
    expect(DB::table('race_payouts')->where('race_id', $raceId)->count())->toBe(0);
});

test('JRA format payout text (ticket type on separate header line) is stored correctly', function () use ($jraSampleText) {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUid($venueId, $now);

    // Act
    $response = $this->actingAs($user)->post(route('races.result.store', ['uid' => $raceUid]), [
        'text' => $jraSampleText,
    ]);

    // Assert
    $response->assertRedirect(route('tickets.index'));
    expect(DB::table('race_payouts')->where('race_id', $raceId)->count())->toBe(12);
});

test('unauthenticated user is redirected to login page when posting race result', function () {
    // Arrange
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceUid' => $raceUid] = createRaceWithUid($venueId, $now);

    // Act
    $response = $this->post(route('races.result.store', ['uid' => $raceUid]), [
        'text' => 'some text',
    ]);

    // Assert
    $response->assertRedirect(route('login'));
});

test('posting race result with non-existent uid returns 404', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->post(route('races.result.store', ['uid' => 'non-existent-uid']), [
        'text' => $sampleText,
    ]);

    // Assert
    $response->assertNotFound();
});

// ===== StoreAction — payout_amount 更新ロジック =====

/**
 * @return array{buyTypeId: int}
 */
function createBuyTypes(CarbonInterface $now): array
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

    $buyTypeId = DB::table('buy_types')->where('name', 'single')->value('id');

    return compact('buyTypeId');
}

test('single: ヒットした馬番の payout_amount が更新される', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUid($venueId, $now);
    createBuyTypes($now);

    $tanshoTicketTypeId = DB::table('ticket_types')->where('name', 'tansho')->value('id');
    $singleBuyTypeId = DB::table('buy_types')->where('name', 'single')->value('id');

    $purchaseId = DB::table('ticket_purchases')->insertGetId([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $tanshoTicketTypeId,
        'buy_type_id' => $singleBuyTypeId,
        'selections' => json_encode(['horses' => [3]]),
        'amount' => 100,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $this->actingAs($user)->post(route('races.result.store', ['uid' => $raceUid]), [
        'text' => $sampleText,
    ]);

    // Assert
    $this->assertDatabaseHas('ticket_purchases', [
        'id' => $purchaseId,
        'payout_amount' => 610,
    ]);
});

test('box: ヒットした組み合わせの payout_amount が更新される', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUid($venueId, $now);
    createBuyTypes($now);

    $umarenTicketTypeId = DB::table('ticket_types')->where('name', 'umaren')->value('id');
    $boxBuyTypeId = DB::table('buy_types')->where('name', 'box')->value('id');

    $purchaseId = DB::table('ticket_purchases')->insertGetId([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $umarenTicketTypeId,
        'buy_type_id' => $boxBuyTypeId,
        'selections' => json_encode(['horses' => [3, 6]]),
        'amount' => 100,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $this->actingAs($user)->post(route('races.result.store', ['uid' => $raceUid]), [
        'text' => $sampleText,
    ]);

    // Assert
    $this->assertDatabaseHas('ticket_purchases', [
        'id' => $purchaseId,
        'payout_amount' => 700,
    ]);
});

test('nagashi: ヒットした組み合わせの payout_amount が更新される', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUid($venueId, $now);
    createBuyTypes($now);

    $umarenTicketTypeId = DB::table('ticket_types')->where('name', 'umaren')->value('id');
    $nagashiBuyTypeId = DB::table('buy_types')->where('name', 'nagashi')->value('id');

    $purchaseId = DB::table('ticket_purchases')->insertGetId([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $umarenTicketTypeId,
        'buy_type_id' => $nagashiBuyTypeId,
        'selections' => json_encode(['axis' => [3], 'others' => [6]]),
        'amount' => 100,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $this->actingAs($user)->post(route('races.result.store', ['uid' => $raceUid]), [
        'text' => $sampleText,
    ]);

    // Assert
    $this->assertDatabaseHas('ticket_purchases', [
        'id' => $purchaseId,
        'payout_amount' => 700,
    ]);
});

test('formation: ヒットした組み合わせの payout_amount が更新される', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUid($venueId, $now);
    createBuyTypes($now);

    $sanrenpukuTicketTypeId = DB::table('ticket_types')->where('name', 'sanrenpuku')->value('id');
    $formationBuyTypeId = DB::table('buy_types')->where('name', 'formation')->value('id');

    $purchaseId = DB::table('ticket_purchases')->insertGetId([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $sanrenpukuTicketTypeId,
        'buy_type_id' => $formationBuyTypeId,
        'selections' => json_encode(['columns' => [[3], [6], [11]]]),
        'amount' => 100,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $this->actingAs($user)->post(route('races.result.store', ['uid' => $raceUid]), [
        'text' => $sampleText,
    ]);

    // Assert
    $this->assertDatabaseHas('ticket_purchases', [
        'id' => $purchaseId,
        'payout_amount' => 1550,
    ]);
});

test('umatan: 着順が一致した場合はヒット、逆順の場合はヒットしない', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUid($venueId, $now);
    createBuyTypes($now);

    $umatanTicketTypeId = DB::table('ticket_types')->where('name', 'umatan')->value('id');
    $singleBuyTypeId = DB::table('buy_types')->where('name', 'single')->value('id');

    // 着順が一致（3→6）
    $hitPurchaseId = DB::table('ticket_purchases')->insertGetId([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $umatanTicketTypeId,
        'buy_type_id' => $singleBuyTypeId,
        'selections' => json_encode(['horses' => [3, 6]]),
        'amount' => 100,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // 逆順（6→3）
    $missedPurchaseId = DB::table('ticket_purchases')->insertGetId([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $umatanTicketTypeId,
        'buy_type_id' => $singleBuyTypeId,
        'selections' => json_encode(['horses' => [6, 3]]),
        'amount' => 100,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $this->actingAs($user)->post(route('races.result.store', ['uid' => $raceUid]), [
        'text' => $sampleText,
    ]);

    // Assert
    $this->assertDatabaseHas('ticket_purchases', [
        'id' => $hitPurchaseId,
        'payout_amount' => 1730,
    ]);
    $this->assertDatabaseHas('ticket_purchases', [
        'id' => $missedPurchaseId,
        'payout_amount' => null,
    ]);
});

test('sanrentan: 着順が一致した場合はヒット、着順が異なる場合はヒットしない', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUid($venueId, $now);
    createBuyTypes($now);

    $sanrentanTicketTypeId = DB::table('ticket_types')->where('name', 'sanrentan')->value('id');
    $singleBuyTypeId = DB::table('buy_types')->where('name', 'single')->value('id');

    // 着順が一致（3→6→11）
    $hitPurchaseId = DB::table('ticket_purchases')->insertGetId([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $sanrentanTicketTypeId,
        'buy_type_id' => $singleBuyTypeId,
        'selections' => json_encode(['horses' => [3, 6, 11]]),
        'amount' => 100,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // 着順が異なる（3→11→6）
    $missedPurchaseId = DB::table('ticket_purchases')->insertGetId([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $sanrentanTicketTypeId,
        'buy_type_id' => $singleBuyTypeId,
        'selections' => json_encode(['horses' => [3, 11, 6]]),
        'amount' => 100,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $this->actingAs($user)->post(route('races.result.store', ['uid' => $raceUid]), [
        'text' => $sampleText,
    ]);

    // Assert
    $this->assertDatabaseHas('ticket_purchases', [
        'id' => $hitPurchaseId,
        'payout_amount' => 8820,
    ]);
    $this->assertDatabaseHas('ticket_purchases', [
        'id' => $missedPurchaseId,
        'payout_amount' => null,
    ]);
});

test('購入した組み合わせがレース結果に含まれない場合、payout_amount は null のまま', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUid($venueId, $now);
    createBuyTypes($now);

    $tanshoTicketTypeId = DB::table('ticket_types')->where('name', 'tansho')->value('id');
    $singleBuyTypeId = DB::table('buy_types')->where('name', 'single')->value('id');

    // 単勝で1番（ヒットしない、結果は3番）
    $purchaseId = DB::table('ticket_purchases')->insertGetId([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $tanshoTicketTypeId,
        'buy_type_id' => $singleBuyTypeId,
        'selections' => json_encode(['horses' => [1]]),
        'amount' => 100,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $this->actingAs($user)->post(route('races.result.store', ['uid' => $raceUid]), [
        'text' => $sampleText,
    ]);

    // Assert
    $this->assertDatabaseHas('ticket_purchases', [
        'id' => $purchaseId,
        'payout_amount' => null,
    ]);
});

test('wide で複数組み合わせがヒットした場合、payout_amount は合算される', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUid($venueId, $now);
    createBuyTypes($now);

    $wideTicketTypeId = DB::table('ticket_types')->where('name', 'wide')->value('id');
    $boxBuyTypeId = DB::table('buy_types')->where('name', 'box')->value('id');

    // ワイド ボックス 3-6-11（3-6: 330円、3-11: 710円、6-11: 340円 → 合計1,380円）
    $purchaseId = DB::table('ticket_purchases')->insertGetId([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $wideTicketTypeId,
        'buy_type_id' => $boxBuyTypeId,
        'selections' => json_encode(['horses' => [3, 6, 11]]),
        'amount' => 300,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $this->actingAs($user)->post(route('races.result.store', ['uid' => $raceUid]), [
        'text' => $sampleText,
    ]);

    // Assert
    $this->assertDatabaseHas('ticket_purchases', [
        'id' => $purchaseId,
        'payout_amount' => 1380,
    ]);
});

test('同じレースの別ユーザーの馬券には影響しない', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUid($venueId, $now);
    createBuyTypes($now);

    $tanshoTicketTypeId = DB::table('ticket_types')->where('name', 'tansho')->value('id');
    $singleBuyTypeId = DB::table('buy_types')->where('name', 'single')->value('id');

    // 投稿ユーザーの馬券（ヒット）
    $userPurchaseId = DB::table('ticket_purchases')->insertGetId([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $tanshoTicketTypeId,
        'buy_type_id' => $singleBuyTypeId,
        'selections' => json_encode(['horses' => [3]]),
        'amount' => 100,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // 別ユーザーの馬券（同じ組み合わせだが更新対象外）
    $otherPurchaseId = DB::table('ticket_purchases')->insertGetId([
        'user_id' => $otherUser->id,
        'race_id' => $raceId,
        'ticket_type_id' => $tanshoTicketTypeId,
        'buy_type_id' => $singleBuyTypeId,
        'selections' => json_encode(['horses' => [3]]),
        'amount' => 100,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $this->actingAs($user)->post(route('races.result.store', ['uid' => $raceUid]), [
        'text' => $sampleText,
    ]);

    // Assert
    $this->assertDatabaseHas('ticket_purchases', [
        'id' => $userPurchaseId,
        'payout_amount' => 610,
    ]);
    $this->assertDatabaseHas('ticket_purchases', [
        'id' => $otherPurchaseId,
        'payout_amount' => null,
    ]);
});

test('別レースの馬券には影響しない', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUid($venueId, $now);

    // 別レース（race_number を変えてユニーク制約を回避）
    $otherRaceId = DB::table('races')->insertGetId([
        'uid' => 'test-uid-other-'.uniqid(),
        'venue_id' => $venueId,
        'race_date' => '2026-04-05',
        'race_number' => 2,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    createBuyTypes($now);

    $tanshoTicketTypeId = DB::table('ticket_types')->where('name', 'tansho')->value('id');
    $singleBuyTypeId = DB::table('buy_types')->where('name', 'single')->value('id');

    // 対象レースの馬券（ヒット）
    $targetPurchaseId = DB::table('ticket_purchases')->insertGetId([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $tanshoTicketTypeId,
        'buy_type_id' => $singleBuyTypeId,
        'selections' => json_encode(['horses' => [3]]),
        'amount' => 100,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // 別レースの馬券（同じ組み合わせだが更新対象外）
    $otherRacePurchaseId = DB::table('ticket_purchases')->insertGetId([
        'user_id' => $user->id,
        'race_id' => $otherRaceId,
        'ticket_type_id' => $tanshoTicketTypeId,
        'buy_type_id' => $singleBuyTypeId,
        'selections' => json_encode(['horses' => [3]]),
        'amount' => 100,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $this->actingAs($user)->post(route('races.result.store', ['uid' => $raceUid]), [
        'text' => $sampleText,
    ]);

    // Assert
    $this->assertDatabaseHas('ticket_purchases', [
        'id' => $targetPurchaseId,
        'payout_amount' => 610,
    ]);
    $this->assertDatabaseHas('ticket_purchases', [
        'id' => $otherRacePurchaseId,
        'payout_amount' => null,
    ]);
});

test('amount が 100円より多い場合、払い戻し金額は購入金額に比例してスケールされる', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUid($venueId, $now);
    createBuyTypes($now);

    $tanshoTicketTypeId = DB::table('ticket_types')->where('name', 'tansho')->value('id');
    $singleBuyTypeId = DB::table('buy_types')->where('name', 'single')->value('id');

    // 単勝3番 200円購入（JRA払戻 610円 × 2口 = 1,220円）
    $purchaseId = DB::table('ticket_purchases')->insertGetId([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $tanshoTicketTypeId,
        'buy_type_id' => $singleBuyTypeId,
        'selections' => json_encode(['horses' => [3]]),
        'amount' => 200,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $this->actingAs($user)->post(route('races.result.store', ['uid' => $raceUid]), [
        'text' => $sampleText,
    ]);

    // Assert
    $this->assertDatabaseHas('ticket_purchases', [
        'id' => $purchaseId,
        'payout_amount' => 1220,
    ]);
});

// ===== GET /races/{uid}/result/edit =====

test('authenticated user can access race result edit page', function () {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceUid' => $raceUid] = createRaceWithUid($venueId, $now);

    // Act
    $response = $this->actingAs($user)->get(route('races.result.edit', ['uid' => $raceUid]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page->component('races/result/edit'));
});

test('unauthenticated user is redirected to login page when accessing race result edit page', function () {
    // Arrange
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceUid' => $raceUid] = createRaceWithUid($venueId, $now);

    // Act
    $response = $this->get(route('races.result.edit', ['uid' => $raceUid]));

    // Assert
    $response->assertRedirect(route('login'));
});

test('accessing race result edit page with non-existent uid returns 404', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->get(route('races.result.edit', ['uid' => 'non-existent-uid']));

    // Assert
    $response->assertNotFound();
});

test('race result edit page response includes payout fields', function () {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUid($venueId, $now);

    $umatanTypeId = DB::table('ticket_types')->where('name', 'umatan')->value('id');
    $payoutId = DB::table('race_payouts')->insertGetId([
        'race_id' => $raceId,
        'ticket_type_id' => $umatanTypeId,
        'payout_amount' => 2410,
        'popularity' => 3,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('race_payout_horses')->insert([
        ['race_payout_id' => $payoutId, 'horse_number' => 3, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
        ['race_payout_id' => $payoutId, 'horse_number' => 5, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.result.edit', ['uid' => $raceUid]));

    // Assert
    $response->assertInertia(fn (Assert $page) =>
        $page->component('races/result/edit')
             ->has('race.payouts.0', fn (Assert $payout) =>
                 $payout->has('ticket_type_label')
                        ->has('ticket_type_name')
                        ->has('payout_amount')
                        ->has('popularity')
                        ->has('horses')
             )
    );
});

test('umatan horses in race result edit page are ordered by sort_order', function () {
    // Arrange
    $user = User::factory()->create();
    ['venueId' => $venueId, 'now' => $now] = createRaceResultMasterData();
    ['raceId' => $raceId, 'raceUid' => $raceUid] = createRaceWithUid($venueId, $now);

    $umatanTypeId = DB::table('ticket_types')->where('name', 'umatan')->value('id');
    $payoutId = DB::table('race_payouts')->insertGetId([
        'race_id' => $raceId,
        'ticket_type_id' => $umatanTypeId,
        'payout_amount' => 2410,
        'popularity' => 3,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    DB::table('race_payout_horses')->insert([
        ['race_payout_id' => $payoutId, 'horse_number' => 3, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
        ['race_payout_id' => $payoutId, 'horse_number' => 5, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.result.edit', ['uid' => $raceUid]));

    // Assert
    $response->assertInertia(fn (Assert $page) =>
        $page->component('races/result/edit')
             ->has('race.payouts.0.horses', 2)
             ->where('race.payouts.0.horses.0.sort_order', 1)
             ->where('race.payouts.0.horses.0.horse_number', 3)
             ->where('race.payouts.0.horses.1.sort_order', 2)
             ->where('race.payouts.0.horses.1.horse_number', 5)
    );
});
