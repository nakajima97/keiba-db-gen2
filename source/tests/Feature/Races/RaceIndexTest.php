<?php

use App\Models\Race;
use App\Models\User;
use App\Models\Venue;
use Inertia\Testing\AssertableInertia as Assert;

// ===== GET /races =====

test('未認証ユーザーがレース一覧にアクセスするとログイン画面にリダイレクトされる', function () {
    // Act
    $response = $this->get(route('races.index'));

    // Assert
    $response->assertRedirectToRoute('login');
});

test('認証済みユーザーはレース一覧にアクセスでき Inertia コンポーネントが描画される', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->get(route('races.index'));

    // Assert
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page->component('races/index'));
});

// ===== GET /races (フィルタリング) =====

test('フィルター未指定のときレース一覧は races と venues が空配列で返る', function () {
    // Arrange
    $user = User::factory()->create();
    $venue = Venue::firstOrCreate(['name' => '東京']);
    Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.index'));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/index')
        ->has('races', 0)
        ->has('venues', 0)
    );
});

test('レース一覧は指定日にレースがある競馬場のみを返す', function () {
    // Arrange
    $user = User::factory()->create();
    $tokyo = Venue::firstOrCreate(['name' => '東京']);
    $nakayama = Venue::firstOrCreate(['name' => '中山']);
    Race::create([
        'venue_id' => $tokyo->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);
    Race::create([
        'venue_id' => $nakayama->id,
        'race_date' => '2026-04-06',
        'race_number' => 1,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.index', ['race_date' => '2026-04-05']));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/index')
        ->has('venues', 1, fn (Assert $venue) => $venue
            ->where('name', '東京')
            ->etc()
        )
    );
});

test('レース一覧は race_date クエリパラメータに合致するレースのみを返す', function () {
    // Arrange
    $user = User::factory()->create();
    $venue = Venue::firstOrCreate(['name' => '東京']);
    Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);
    Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-06',
        'race_number' => 2,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.index', ['race_date' => '2026-04-05']));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/index')
        ->has('races', 1, fn (Assert $race) => $race
            ->where('race_date', '2026-04-05')
            ->etc()
        )
    );
});

test('race_date と venue_id を併せて指定するとレース一覧は両方に合致するレースのみを返す', function () {
    // Arrange
    $user = User::factory()->create();
    $tokyo = Venue::firstOrCreate(['name' => '東京']);
    $nakayama = Venue::firstOrCreate(['name' => '中山']);
    Race::create([
        'venue_id' => $tokyo->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);
    Race::create([
        'venue_id' => $nakayama->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.index', ['race_date' => '2026-04-05', 'venue_id' => $tokyo->id]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/index')
        ->has('races', 1, fn (Assert $race) => $race
            ->where('venue_name', '東京')
            ->etc()
        )
    );
});

test('レース一覧の Inertia props は各レース項目に race_name を含む', function () {
    // Arrange
    $user = User::factory()->create();
    $venue = Venue::firstOrCreate(['name' => '東京']);
    Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
        'race_name' => '天皇賞（春）',
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.index', ['race_date' => '2026-04-05', 'venue_id' => $venue->id]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/index')
        ->has('races', 1, fn (Assert $race) => $race
            ->has('race_name')
            ->etc()
        )
    );
});
