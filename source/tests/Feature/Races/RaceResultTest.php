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
