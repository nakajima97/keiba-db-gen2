<?php

use App\Models\Horse;
use App\Models\User;
use App\UseCases\Horse\IndexAction;
use Inertia\Testing\AssertableInertia as Assert;

// ===== GET /horses =====

test('未認証ユーザーが競走馬検索にアクセスするとログイン画面にリダイレクトされる', function () {
    // Act
    $response = $this->get(route('horses.index'));

    // Assert
    $response->assertRedirectToRoute('login');
});

test('キーワード未指定の場合は検索結果が空で keyword は null になる', function () {
    // Arrange
    $user = User::factory()->create();
    Horse::create(['name' => 'テストホース', 'birth_year' => 2020]);

    // Act
    $response = $this->actingAs($user)->get(route('horses.index'));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('horses/index')
        ->has('horses', 0)
        ->where('filters.keyword', null)
    );
});

test('馬名の部分一致で検索でき uid・馬名・生年を馬名の昇順で返す', function () {
    // Arrange
    $user = User::factory()->create();
    $horseB = Horse::create(['name' => 'サンプルホースB', 'birth_year' => 2020]);
    $horseA = Horse::create(['name' => 'サンプルホースA', 'birth_year' => 2019]);
    Horse::create(['name' => 'ベツノウマ', 'birth_year' => 2019]);

    // Act
    $response = $this->actingAs($user)->get(route('horses.index', ['keyword' => 'プルホ']));

    // Assert
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('horses/index')
        ->has('horses', 2)
        ->has('horses.0', fn (Assert $item) => $item
            ->where('uid', $horseA->uid)
            ->where('name', 'サンプルホースA')
            ->where('birth_year', 2019)
        )
        ->where('horses.1.uid', $horseB->uid)
        ->where('filters.keyword', 'プルホ')
    );
});

test('キーワード中の % と _ はワイルドカードではなく文字として扱われる', function () {
    // Arrange
    $user = User::factory()->create();
    Horse::create(['name' => 'サンプルホース', 'birth_year' => 2020]);
    Horse::create(['name' => '100%ホース', 'birth_year' => 2020]);

    // Act
    $percentResponse = $this->actingAs($user)->get(route('horses.index', ['keyword' => '%']));
    $underscoreResponse = $this->actingAs($user)->get(route('horses.index', ['keyword' => '_']));

    // Assert
    $percentResponse->assertInertia(fn (Assert $page) => $page
        ->has('horses', 1)
        ->where('horses.0.name', '100%ホース')
    );
    $underscoreResponse->assertInertia(fn (Assert $page) => $page->has('horses', 0));
});

test('検索結果は最大件数までに制限される', function () {
    // Arrange
    $user = User::factory()->create();
    for ($i = 0; $i < IndexAction::LIMIT + 1; $i++) {
        Horse::create(['name' => sprintf('ホース%03d', $i), 'birth_year' => 2020]);
    }

    // Act
    $response = $this->actingAs($user)->get(route('horses.index', ['keyword' => 'ホース']));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page->has('horses', IndexAction::LIMIT));
});
