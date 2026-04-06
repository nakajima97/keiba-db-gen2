<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

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

    $raceId = DB::table('races')->insertGetId([
        'venue_id' => $venueId,
        'race_date' => '2026-04-05',
        'race_number' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('ticket_purchases')->insert([
        'user_id' => $user->id,
        'race_id' => $raceId,
        'ticket_type_id' => $ticketTypeId,
        'buy_type_id' => $buyTypeId,
        'selections' => json_encode(['axis' => [3], 'others' => [1, 5]]),
        'amount' => 200,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('ticket_purchases')->insert([
        'user_id' => $otherUser->id,
        'race_id' => $raceId,
        'ticket_type_id' => $ticketTypeId,
        'buy_type_id' => $buyTypeId,
        'selections' => json_encode(['axis' => [1], 'others' => [2, 4]]),
        'amount' => 300,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('tickets.index'));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('tickets/index')
        ->where('purchases', function ($purchases) use ($user, $otherUser) {
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

    $olderRaceId = DB::table('races')->insertGetId([
        'venue_id' => $venueId,
        'race_date' => '2026-04-01',
        'race_number' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $newerRaceId = DB::table('races')->insertGetId([
        'venue_id' => $venueId,
        'race_date' => '2026-04-05',
        'race_number' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('ticket_purchases')->insert([
        'user_id' => $user->id,
        'race_id' => $olderRaceId,
        'ticket_type_id' => $ticketTypeId,
        'buy_type_id' => $buyTypeId,
        'selections' => json_encode(['axis' => [3], 'others' => [1, 5]]),
        'amount' => 100,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('ticket_purchases')->insert([
        'user_id' => $user->id,
        'race_id' => $newerRaceId,
        'ticket_type_id' => $ticketTypeId,
        'buy_type_id' => $buyTypeId,
        'selections' => json_encode(['axis' => [1], 'others' => [2, 4]]),
        'amount' => 200,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

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

    $race1Id = DB::table('races')->insertGetId([
        'venue_id' => $venueId,
        'race_date' => '2026-04-05',
        'race_number' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $race5Id = DB::table('races')->insertGetId([
        'venue_id' => $venueId,
        'race_date' => '2026-04-05',
        'race_number' => 5,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('ticket_purchases')->insert([
        'user_id' => $user->id,
        'race_id' => $race1Id,
        'ticket_type_id' => $ticketTypeId,
        'buy_type_id' => $buyTypeId,
        'selections' => json_encode(['axis' => [3], 'others' => [1, 5]]),
        'amount' => 100,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('ticket_purchases')->insert([
        'user_id' => $user->id,
        'race_id' => $race5Id,
        'ticket_type_id' => $ticketTypeId,
        'buy_type_id' => $buyTypeId,
        'selections' => json_encode(['axis' => [1], 'others' => [2, 4]]),
        'amount' => 200,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

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

    for ($i = 1; $i <= 12; $i++) {
        $raceId = DB::table('races')->insertGetId([
            'venue_id' => $venueId,
            'race_date' => '2026-04-05',
            'race_number' => $i,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

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

    for ($raceNumber = 1; $raceNumber <= 12; $raceNumber++) {
        DB::table('races')->insertGetId([
            'venue_id' => $venueId,
            'race_date' => '2026-04-05',
            'race_number' => $raceNumber,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
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
            $raceId = DB::table('races')->insertGetId([
                'venue_id' => $venueId,
                'race_date' => $date,
                'race_number' => $raceNumber,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

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

    for ($raceNumber = 1; $raceNumber <= 12; $raceNumber++) {
        DB::table('races')->insertGetId([
            'venue_id' => $venueId,
            'race_date' => '2026-04-05',
            'race_number' => $raceNumber,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
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
            $raceId = DB::table('races')->insertGetId([
                'venue_id' => $venueId,
                'race_date' => $date,
                'race_number' => $raceNumber,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

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
