<?php

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
        'uid' => Str::random(21),
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
