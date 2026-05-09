<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

// ===== GET /races/new =====

test('未認証ユーザーがレース作成画面にアクセスするとログイン画面にリダイレクトされる', function () {
    // Act
    $response = $this->get(route('races.create'));

    // Assert
    $response->assertRedirectToRoute('login');
});

test('認証済みユーザーは venues prop 付きでレース作成画面にアクセスできる', function () {
    // Arrange
    $user = User::factory()->create();
    $now = now();
    DB::table('venues')->insert([
        ['name' => '東京', 'created_at' => $now, 'updated_at' => $now],
        ['name' => '阪神', 'created_at' => $now, 'updated_at' => $now],
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.create'));

    // Assert
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/new')
        ->has('venues', 2)
    );
});

test('race_date クエリパラメータはセッションの last_race_date より優先される', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)
        ->withSession(['last_race_date' => '2026-01-01'])
        ->get(route('races.create', ['race_date' => '2026-05-01']));

    // Assert
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('last_race_date', '2026-05-01')
    );
});

test('race_date クエリパラメータが無いときセッションの last_race_date が使われる', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)
        ->withSession(['last_race_date' => '2026-01-01'])
        ->get(route('races.create'));

    // Assert
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('last_race_date', '2026-01-01')
    );
});

test('race_date クエリパラメータが不正のとき無視されセッションの last_race_date にフォールバックする', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)
        ->withSession(['last_race_date' => '2026-01-01'])
        ->get(route('races.create', ['race_date' => 'invalid-date']));

    // Assert
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('last_race_date', '2026-01-01')
    );
});
