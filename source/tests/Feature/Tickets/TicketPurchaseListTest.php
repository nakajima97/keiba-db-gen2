<?php

use App\Models\User;
use App\Support\NanoId;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @return array{venueId: int, ticketTypeId: int, buyTypeId: int, now: CarbonInterface}
 */
function createMasterData(): array
{
    $now = now();

    DB::table('venues')->insert(['name' => '東京', 'created_at' => $now, 'updated_at' => $now]);
    $venueId = DB::table('venues')->where('name', '東京')->value('id');

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

    return compact('venueId', 'ticketTypeId', 'buyTypeId', 'now');
}

function createRace(int $venueId, string $date, int $raceNumber, CarbonInterface $now): int
{
    return DB::table('races')->insertGetId([
        'uid' => NanoId::generate(),
        'venue_id' => $venueId,
        'race_date' => $date,
        'race_number' => $raceNumber,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}

function createTicketPurchase(int $userId, int $raceId, int $ticketTypeId, int $buyTypeId, CarbonInterface $now, int $amount = 100): void
{
    DB::table('ticket_purchases')->insert([
        'user_id' => $userId,
        'race_id' => $raceId,
        'ticket_type_id' => $ticketTypeId,
        'buy_type_id' => $buyTypeId,
        'selections' => json_encode(['axis' => [1], 'others' => [2, 3]]),
        'amount' => $amount,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}

test('authenticated user can access ticket list page', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->get(route('tickets.index'));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('tickets/index')
        ->has('purchases')
        ->has('nextCursor')
    );
});

test('purchases property contains only the authenticated user\'s data', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    ['venueId' => $venueId, 'ticketTypeId' => $ticketTypeId, 'buyTypeId' => $buyTypeId, 'now' => $now] = createMasterData();
    $raceId = createRace($venueId, '2026-04-05', 1, $now);

    createTicketPurchase($user->id, $raceId, $ticketTypeId, $buyTypeId, $now, 200);
    createTicketPurchase($otherUser->id, $raceId, $ticketTypeId, $buyTypeId, $now, 300);

    // Act
    $response = $this->actingAs($user)->get(route('tickets.index'));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('tickets/index')
        ->where('purchases', function ($purchases) {
            expect(count($purchases))->toBe(1);
            expect($purchases[0]['user_id'] ?? null)->toBeNull();

            return true;
        })
    );
});

test('purchases is empty array when user has no purchases', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->get(route('tickets.index'));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('tickets/index')
        ->where('purchases', [])
    );
});

test('purchases are sorted by race_date descending', function () {
    // Arrange
    $user = User::factory()->create();

    ['venueId' => $venueId, 'ticketTypeId' => $ticketTypeId, 'buyTypeId' => $buyTypeId, 'now' => $now] = createMasterData();

    $olderRaceId = createRace($venueId, '2026-04-01', 1, $now);
    $newerRaceId = createRace($venueId, '2026-04-05', 1, $now);

    createTicketPurchase($user->id, $olderRaceId, $ticketTypeId, $buyTypeId, $now);
    createTicketPurchase($user->id, $newerRaceId, $ticketTypeId, $buyTypeId, $now);

    // Act
    $response = $this->actingAs($user)->get(route('tickets.index'));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('tickets/index')
        ->where('purchases', function ($purchases) {
            expect($purchases[0]['race_date'])->toBe('2026-04-05');
            expect($purchases[1]['race_date'])->toBe('2026-04-01');

            return true;
        })
    );
});

test('purchases on same date are sorted by race_number descending', function () {
    // Arrange
    $user = User::factory()->create();

    ['venueId' => $venueId, 'ticketTypeId' => $ticketTypeId, 'buyTypeId' => $buyTypeId, 'now' => $now] = createMasterData();

    $race1Id = createRace($venueId, '2026-04-05', 1, $now);
    $race5Id = createRace($venueId, '2026-04-05', 5, $now);

    createTicketPurchase($user->id, $race1Id, $ticketTypeId, $buyTypeId, $now, 100);
    createTicketPurchase($user->id, $race5Id, $ticketTypeId, $buyTypeId, $now, 200);

    // Act
    $response = $this->actingAs($user)->get(route('tickets.index'));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('tickets/index')
        ->where('purchases', function ($purchases) {
            expect($purchases[0]['race_number'])->toBe(5);
            expect($purchases[1]['race_number'])->toBe(1);

            return true;
        })
    );
});

test('nextCursor is null when purchases are 30 or fewer', function () {
    // Arrange
    $user = User::factory()->create();

    ['venueId' => $venueId, 'ticketTypeId' => $ticketTypeId, 'buyTypeId' => $buyTypeId, 'now' => $now] = createMasterData();

    for ($i = 1; $i <= 12; $i++) {
        $raceId = createRace($venueId, '2026-04-05', $i, $now);
        createTicketPurchase($user->id, $raceId, $ticketTypeId, $buyTypeId, $now);
    }

    // Act
    $response = $this->actingAs($user)->get(route('tickets.index'));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('tickets/index')
        ->where('nextCursor', null)
    );
});

test('nextCursor is non-null when purchases exceed 30', function () {
    // Arrange
    $user = User::factory()->create();

    ['venueId' => $venueId, 'ticketTypeId' => $ticketTypeId, 'buyTypeId' => $buyTypeId, 'now' => $now] = createMasterData();

    for ($raceNumber = 1; $raceNumber <= 12; $raceNumber++) {
        createRace($venueId, '2026-04-05', $raceNumber, $now);
    }

    for ($day = 1; $day <= 31; $day++) {
        $date = sprintf('2026-%02d-%02d', intdiv($day, 12) + 3, ($day % 12) + 1);
        $raceNumber = ($day % 12) + 1;

        $raceId = DB::table('races')
            ->where('venue_id', $venueId)
            ->where('race_date', '2026-04-05')
            ->where('race_number', $raceNumber)
            ->value('id');

        if (! $raceId) {
            $raceId = createRace($venueId, $date, $raceNumber, $now);
        }

        createTicketPurchase($user->id, $raceId, $ticketTypeId, $buyTypeId, $now);
    }

    // Act
    $response = $this->actingAs($user)->get(route('tickets.index'));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('tickets/index')
        ->where('purchases', function ($purchases) {
            expect(count($purchases))->toBe(30);

            return true;
        })
        ->where('nextCursor', fn ($cursor) => $cursor !== null)
    );
});

test('cursor pagination returns next 30 items', function () {
    // Arrange
    $user = User::factory()->create();

    ['venueId' => $venueId, 'ticketTypeId' => $ticketTypeId, 'buyTypeId' => $buyTypeId, 'now' => $now] = createMasterData();

    for ($raceNumber = 1; $raceNumber <= 12; $raceNumber++) {
        createRace($venueId, '2026-04-05', $raceNumber, $now);
    }

    for ($day = 1; $day <= 35; $day++) {
        $date = sprintf('2026-%02d-%02d', intdiv($day, 12) + 3, ($day % 12) + 1);
        $raceNumber = ($day % 12) + 1;

        $raceId = DB::table('races')
            ->where('venue_id', $venueId)
            ->where('race_date', '2026-04-05')
            ->where('race_number', $raceNumber)
            ->value('id');

        if (! $raceId) {
            $raceId = createRace($venueId, $date, $raceNumber, $now);
        }

        createTicketPurchase($user->id, $raceId, $ticketTypeId, $buyTypeId, $now);
    }

    $firstResponse = $this->actingAs($user)->get(route('tickets.index'));
    $cursor = $firstResponse->viewData('page')['props']['nextCursor'];

    // Act
    $response = $this->actingAs($user)->get(route('tickets.index', ['cursor' => $cursor]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('tickets/index')
        ->where('purchases', function ($purchases) {
            expect(count($purchases))->toBeGreaterThanOrEqual(1);

            return true;
        })
    );
});

test('unauthenticated user is redirected to login page', function () {
    // Act
    $response = $this->get(route('tickets.index'));

    // Assert
    $response->assertRedirect(route('login'));
});

// ===== payout_amount =====

test('payout_amount が null の馬券: レスポンスの purchases に payout_amount が null で含まれる', function () {
    // Arrange
    $user = User::factory()->create();

    ['venueId' => $venueId, 'ticketTypeId' => $ticketTypeId, 'buyTypeId' => $buyTypeId, 'now' => $now] = createMasterData();
    $raceId = createRace($venueId, '2026-04-05', 1, $now);

    createTicketPurchase($user->id, $raceId, $ticketTypeId, $buyTypeId, $now);

    // Act
    $response = $this->actingAs($user)->get(route('tickets.index'));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('tickets/index')
        ->where('purchases', function ($purchases) {
            expect($purchases[0]['payout_amount'])->toBeNull();

            return true;
        })
    );
});

test('payout_amount が設定済みの馬券: レスポンスの purchases に正しい値が含まれる', function () {
    // Arrange
    $user = User::factory()->create();

    ['venueId' => $venueId, 'ticketTypeId' => $ticketTypeId, 'buyTypeId' => $buyTypeId, 'now' => $now] = createMasterData();
    $raceId = createRace($venueId, '2026-04-05', 1, $now);

    createTicketPurchase($user->id, $raceId, $ticketTypeId, $buyTypeId, $now);

    DB::table('ticket_purchases')
        ->where('user_id', $user->id)
        ->where('race_id', $raceId)
        ->update(['payout_amount' => 5000]);

    // Act
    $response = $this->actingAs($user)->get(route('tickets.index'));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('tickets/index')
        ->where('purchases', function ($purchases) {
            expect($purchases[0]['payout_amount'])->toBe(5000);

            return true;
        })
    );
});

// ===== 合計金額の計算 =====

test('nagashi（流し）・2点: amount が 単価×2 で返される', function () {
    // Arrange
    $user = User::factory()->create();

    ['venueId' => $venueId, 'ticketTypeId' => $ticketTypeId, 'buyTypeId' => $buyTypeId, 'now' => $now] = createMasterData();
    $raceId = createRace($venueId, '2026-04-05', 1, $now);

    DB::table('ticket_purchases')->insert([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $ticketTypeId,
        'buy_type_id' => $buyTypeId,
        'selections' => json_encode(['axis' => [1], 'others' => [2, 3]]),
        'amount' => 100,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('tickets.index'));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('tickets/index')
        ->where('purchases', function ($purchases) {
            expect($purchases[0]['amount'])->toBe(200);

            return true;
        })
    );
});

test('box（ボックス）・3点: amount が 単価×3 で返される', function () {
    // Arrange
    $user = User::factory()->create();

    ['venueId' => $venueId, 'ticketTypeId' => $ticketTypeId, 'now' => $now] = createMasterData();
    $raceId = createRace($venueId, '2026-04-05', 1, $now);

    $boxBuyTypeId = DB::table('buy_types')->insertGetId([
        'name' => 'box',
        'label' => 'ボックス',
        'sort_order' => 3,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('ticket_purchases')->insert([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $ticketTypeId,
        'buy_type_id' => $boxBuyTypeId,
        'selections' => json_encode(['horses' => [1, 2, 3]]),
        'amount' => 100,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('tickets.index'));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('tickets/index')
        ->where('purchases', function ($purchases) {
            expect($purchases[0]['amount'])->toBe(300);

            return true;
        })
    );
});

test('formation（フォーメーション）・重複なし: amount が 単価×全点数 で返される', function () {
    // Arrange
    $user = User::factory()->create();

    ['venueId' => $venueId, 'now' => $now] = createMasterData();
    $raceId = createRace($venueId, '2026-04-05', 1, $now);

    $sanrentanTicketTypeId = DB::table('ticket_types')->insertGetId([
        'name' => 'sanrentan',
        'label' => '三連単',
        'sort_order' => 6,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $formationBuyTypeId = DB::table('buy_types')->insertGetId([
        'name' => 'formation',
        'label' => 'フォーメーション',
        'sort_order' => 4,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('ticket_purchases')->insert([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $sanrentanTicketTypeId,
        'buy_type_id' => $formationBuyTypeId,
        'selections' => json_encode(['columns' => [[1, 2], [3, 4], [5, 6]]]),
        'amount' => 100,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('tickets.index'));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('tickets/index')
        ->where('purchases', function ($purchases) {
            expect($purchases[0]['amount'])->toBe(800);

            return true;
        })
    );
});

test('formation（フォーメーション）・重複馬番あり: 有効点数×単価 で返される', function () {
    // Arrange
    $user = User::factory()->create();

    ['venueId' => $venueId, 'now' => $now] = createMasterData();
    $raceId = createRace($venueId, '2026-04-05', 1, $now);

    $sanrentanTicketTypeId = DB::table('ticket_types')->insertGetId([
        'name' => 'sanrentan',
        'label' => '三連単',
        'sort_order' => 6,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $formationBuyTypeId = DB::table('buy_types')->insertGetId([
        'name' => 'formation',
        'label' => 'フォーメーション',
        'sort_order' => 4,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('ticket_purchases')->insert([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $sanrentanTicketTypeId,
        'buy_type_id' => $formationBuyTypeId,
        'selections' => json_encode(['columns' => [[1, 2], [1, 3], [4, 5]]]),
        'amount' => 600,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('tickets.index'));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('tickets/index')
        ->where('purchases', function ($purchases) {
            expect($purchases[0]['amount'])->toBe(3600);

            return true;
        })
    );
});

test('amount が null の場合: amount が null で返される', function () {
    // Arrange
    $user = User::factory()->create();

    ['venueId' => $venueId, 'ticketTypeId' => $ticketTypeId, 'buyTypeId' => $buyTypeId, 'now' => $now] = createMasterData();
    $raceId = createRace($venueId, '2026-04-05', 1, $now);

    DB::table('ticket_purchases')->insert([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $ticketTypeId,
        'buy_type_id' => $buyTypeId,
        'selections' => json_encode(['axis' => [1], 'others' => [2, 3]]),
        'amount' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('tickets.index'));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('tickets/index')
        ->where('purchases', function ($purchases) {
            expect($purchases[0]['amount'])->toBeNull();

            return true;
        })
    );
});

test('purchases on same date are sorted by venue_name descending', function () {
    // Arrange
    $user = User::factory()->create();

    ['venueId' => $tokyoVenueId, 'ticketTypeId' => $ticketTypeId, 'buyTypeId' => $buyTypeId, 'now' => $now] = createMasterData();

    DB::table('venues')->insert(['name' => '中山', 'created_at' => $now, 'updated_at' => $now]);
    $nakayamaVenueId = DB::table('venues')->where('name', '中山')->value('id');

    $tokyoRaceId = createRace($tokyoVenueId, '2026-04-05', 1, $now);
    $nakayamaRaceId = createRace($nakayamaVenueId, '2026-04-05', 1, $now);

    createTicketPurchase($user->id, $tokyoRaceId, $ticketTypeId, $buyTypeId, $now, 100);
    createTicketPurchase($user->id, $nakayamaRaceId, $ticketTypeId, $buyTypeId, $now, 200);

    // Act
    $response = $this->actingAs($user)->get(route('tickets.index'));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('tickets/index')
        ->where('purchases', function ($purchases) {
            expect(count($purchases))->toBe(2);

            $firstVenueName = $purchases[0]['venue_name'];
            $secondVenueName = $purchases[1]['venue_name'];

            expect($firstVenueName >= $secondVenueName)->toBeTrue();

            return true;
        })
    );
});

// ===== num_combinations =====

test('num_combinations: nagashi・2点 の馬券で num_combinations が 2 で返される', function () {
    // Arrange
    $user = User::factory()->create();

    ['venueId' => $venueId, 'ticketTypeId' => $ticketTypeId, 'buyTypeId' => $buyTypeId, 'now' => $now] = createMasterData();
    $raceId = createRace($venueId, '2026-04-05', 1, $now);

    createTicketPurchase($user->id, $raceId, $ticketTypeId, $buyTypeId, $now, 100);

    // Act
    $response = $this->actingAs($user)->get(route('tickets.index'));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('tickets/index')
        ->where('purchases', function ($purchases) {
            expect($purchases[0]['num_combinations'])->toBe(2);

            return true;
        })
    );
});

test('num_combinations: amount が null でも num_combinations が返される', function () {
    // Arrange
    $user = User::factory()->create();

    ['venueId' => $venueId, 'ticketTypeId' => $ticketTypeId, 'buyTypeId' => $buyTypeId, 'now' => $now] = createMasterData();
    $raceId = createRace($venueId, '2026-04-05', 1, $now);

    DB::table('ticket_purchases')->insert([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $ticketTypeId,
        'buy_type_id' => $buyTypeId,
        'selections' => json_encode(['axis' => [1], 'others' => [2, 3]]),
        'amount' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('tickets.index'));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('tickets/index')
        ->where('purchases', function ($purchases) {
            expect($purchases[0]['num_combinations'])->toBe(2);

            return true;
        })
    );
});
