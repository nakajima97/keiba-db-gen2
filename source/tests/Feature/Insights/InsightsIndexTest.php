<?php

use App\Models\BuyType;
use App\Models\Race;
use App\Models\RaceResultHorse;
use App\Models\TicketPurchase;
use App\Models\TicketType;
use App\Models\User;
use Database\Seeders\BuyTypeSeeder;
use Database\Seeders\TicketTypeSeeder;

beforeEach(function () {
    $this->seed(TicketTypeSeeder::class);
    $this->seed(BuyTypeSeeder::class);
});

test('ゲストはログイン画面にリダイレクトされる', function () {
    // Act
    $response = $this->get(route('insights'));

    // Assert
    $response->assertRedirect(route('login'));
});

test('認証済みユーザーはインサイト画面にアクセスできる', function () {
    // Arrange
    $user = User::factory()->create();
    $this->actingAs($user);

    // Act
    $response = $this->get(route('insights'));

    // Assert
    $response->assertOk();
});

test('period クエリパラメータが受け付けられる', function () {
    // Arrange
    $user = User::factory()->create();
    $this->actingAs($user);

    // Act
    $response = $this->get(route('insights', ['period' => '3m']));

    // Assert
    $response->assertOk();
});

test('Inertia レスポンスに必要なプロパティが含まれる', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->get(route('insights'));

    // Assert
    $response->assertInertia(fn ($page) => $page
        ->has('summary')
        ->has('patternBreakdown')
        ->has('popularityPatternMatrix')
        ->has('popularityReturns')
        ->has('monthlyTrends')
        ->has('recentSamples')
        ->has('period')
    );
});

test('period を省略した場合は 1m がデフォルトとして返る', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->get(route('insights'));

    // Assert
    $response->assertInertia(fn ($page) => $page
        ->where('period', '1m')
    );
});

/**
 * Insights テスト用の馬券購入データを作成するヘルパー。
 *
 * @param  array<string, mixed>  $selections
 * @param  list<array{horse_number: int, finishing_order: int, popularity: int}>  $resultHorses  `[]` のときレース結果未登録扱い（race_result_horses を作らない）。
 */
function createInsightsTicket(
    User $user,
    string $raceDate,
    string $buyType,
    string $ticketType,
    array $selections,
    int $unitStake = 1000,
    ?int $payoutAmount = 0,
    array $resultHorses = [],
): void {
    $race = Race::factory()->create(['race_date' => $raceDate]);

    TicketPurchase::factory()->create([
        'user_id' => $user->id,
        'race_id' => $race->id,
        'ticket_type_id' => TicketType::where('name', $ticketType)->value('id'),
        'buy_type_id' => BuyType::where('name', $buyType)->value('id'),
        'selections' => $selections,
        'unit_stake' => $unitStake,
        'payout_amount' => $payoutAmount,
    ]);

    foreach ($resultHorses as $horse) {
        RaceResultHorse::factory()->create([
            'race_id' => $race->id,
            'finishing_order' => $horse['finishing_order'],
            'horse_number' => $horse['horse_number'],
            'popularity' => $horse['popularity'],
        ]);
    }
}

/**
 * 馬番1〜8で着順=人気=馬番のシンプルな結果セットを返す。
 * パターン分類テストで「1着=馬番1」「2着=馬番2」を前提にしたい場面で使う。
 *
 * @return list<array{horse_number: int, finishing_order: int, popularity: int}>
 */
function defaultResultHorses(): array
{
    $horses = [];
    for ($i = 1; $i <= 8; $i++) {
        $horses[] = [
            'horse_number' => $i,
            'finishing_order' => $i,
            'popularity' => $i,
        ];
    }

    return $horses;
}

test('buy_type=nagashi かつ ticket_type=umaren 以外の馬券は集計対象に含まれない', function () {
    // Arrange
    $user = User::factory()->create();
    createInsightsTicket(
        user: $user,
        raceDate: now()->subDays(3)->toDateString(),
        buyType: 'single',
        ticketType: 'tansho',
        selections: ['horses' => [1]],
        payoutAmount: 1500,
        resultHorses: defaultResultHorses(),
    );

    // Act
    $response = $this->actingAs($user)->get(route('insights'));

    // Assert
    // summary は null（集計対象が存在しないため）
    $response->assertInertia(fn ($page) => $page
        ->where('summary', null)
    );
});

test('レース結果が未登録（race_result_horses が無い）の馬券は集計対象に含まれない', function () {
    // Arrange
    $user = User::factory()->create();
    createInsightsTicket(
        user: $user,
        raceDate: now()->subDays(3)->toDateString(),
        buyType: 'nagashi',
        ticketType: 'umaren',
        selections: ['axis' => [1], 'others' => [2, 3]],
    );

    // Act
    $response = $this->actingAs($user)->get(route('insights'));

    // Assert
    // summary は null（結果未登録の馬券は集計対象外）
    $response->assertInertia(fn ($page) => $page
        ->where('summary', null)
    );
});

test('period=1m のとき直近1ヶ月のデータのみ集計される', function () {
    // Arrange
    $user = User::factory()->create();
    // 2ヶ月前（含まれない想定）
    createInsightsTicket(
        user: $user,
        raceDate: now()->subMonths(2)->toDateString(),
        buyType: 'nagashi',
        ticketType: 'umaren',
        selections: ['axis' => [1], 'others' => [2, 3]],
        resultHorses: defaultResultHorses(),
    );
    // 直近（含まれる想定）
    createInsightsTicket(
        user: $user,
        raceDate: now()->subDays(3)->toDateString(),
        buyType: 'nagashi',
        ticketType: 'umaren',
        selections: ['axis' => [1], 'others' => [2, 3]],
        resultHorses: defaultResultHorses(),
    );

    // Act
    $response = $this->actingAs($user)->get(route('insights', ['period' => '1m']));

    // Assert
    $response->assertInertia(fn ($page) => $page
        ->where('summary.total_tickets', 1)
    );
});

test('period=all のとき期間制限なく全データが集計される', function () {
    // Arrange
    $user = User::factory()->create();
    // 2ヶ月前
    createInsightsTicket(
        user: $user,
        raceDate: now()->subMonths(2)->toDateString(),
        buyType: 'nagashi',
        ticketType: 'umaren',
        selections: ['axis' => [1], 'others' => [2, 3]],
        resultHorses: defaultResultHorses(),
    );
    // 直近
    createInsightsTicket(
        user: $user,
        raceDate: now()->subDays(3)->toDateString(),
        buyType: 'nagashi',
        ticketType: 'umaren',
        selections: ['axis' => [1], 'others' => [2, 3]],
        resultHorses: defaultResultHorses(),
    );

    // Act
    $response = $this->actingAs($user)->get(route('insights', ['period' => 'all']));

    // Assert
    $response->assertInertia(fn ($page) => $page
        ->where('summary.total_tickets', 2)
    );
});

test('軸が1〜2着・相手も1〜2着のとき pattern=hit に分類される', function () {
    // Arrange
    $user = User::factory()->create();
    // 1着=馬番1, 2着=馬番2
    createInsightsTicket(
        user: $user,
        raceDate: now()->subDays(3)->toDateString(),
        buyType: 'nagashi',
        ticketType: 'umaren',
        selections: ['axis' => [1], 'others' => [2, 5]],
        resultHorses: defaultResultHorses(),
    );

    // Act
    $response = $this->actingAs($user)->get(route('insights'));

    // Assert
    $response->assertInertia(fn ($page) => $page
        ->where('patternBreakdown.0.pattern', 'hit')
        ->where('patternBreakdown.0.count', 1)
    );
});

test('軸のみ1〜2着・相手は3着以下のとき pattern=axis_only に分類される', function () {
    // Arrange
    $user = User::factory()->create();
    // 1着=馬番1（軸）, 相手=馬番5,6（3着以下）
    createInsightsTicket(
        user: $user,
        raceDate: now()->subDays(3)->toDateString(),
        buyType: 'nagashi',
        ticketType: 'umaren',
        selections: ['axis' => [1], 'others' => [5, 6]],
        resultHorses: defaultResultHorses(),
    );

    // Act
    $response = $this->actingAs($user)->get(route('insights'));

    // Assert
    $response->assertInertia(fn ($page) => $page
        ->where('patternBreakdown.1.pattern', 'axis_only')
        ->where('patternBreakdown.1.count', 1)
    );
});

test('相手のみ1〜2着・軸は3着以下のとき pattern=others_only に分類される', function () {
    // Arrange
    $user = User::factory()->create();
    // 1着=馬番1, 2着=馬番2（相手）, 軸=馬番5
    createInsightsTicket(
        user: $user,
        raceDate: now()->subDays(3)->toDateString(),
        buyType: 'nagashi',
        ticketType: 'umaren',
        selections: ['axis' => [5], 'others' => [1, 2]],
        resultHorses: defaultResultHorses(),
    );

    // Act
    $response = $this->actingAs($user)->get(route('insights'));

    // Assert
    $response->assertInertia(fn ($page) => $page
        ->where('patternBreakdown.2.pattern', 'others_only')
        ->where('patternBreakdown.2.count', 1)
    );
});

test('軸も相手も1〜2着外のとき pattern=miss に分類される', function () {
    // Arrange
    $user = User::factory()->create();
    // 1着=馬番1, 2着=馬番2 だが、軸=5・相手=6,7
    createInsightsTicket(
        user: $user,
        raceDate: now()->subDays(3)->toDateString(),
        buyType: 'nagashi',
        ticketType: 'umaren',
        selections: ['axis' => [5], 'others' => [6, 7]],
        resultHorses: defaultResultHorses(),
    );

    // Act
    $response = $this->actingAs($user)->get(route('insights'));

    // Assert
    $response->assertInertia(fn ($page) => $page
        ->where('patternBreakdown.3.pattern', 'miss')
        ->where('patternBreakdown.3.count', 1)
    );
});

test('軸の最良 popularity=3 のとき band=top に集計される', function () {
    // Arrange
    $user = User::factory()->create();
    // popularity=3 の馬番を軸にする（band=top の境界値）
    createInsightsTicket(
        user: $user,
        raceDate: now()->subDays(3)->toDateString(),
        buyType: 'nagashi',
        ticketType: 'umaren',
        selections: ['axis' => [3], 'others' => [4, 5]],
        resultHorses: defaultResultHorses(),
    );

    // Act
    $response = $this->actingAs($user)->get(route('insights'));

    // Assert: popularityReturns[0] が top（件数が 1）
    $response->assertInertia(fn ($page) => $page
        ->where('popularityReturns.0.popularity', 'top')
        ->where('popularityReturns.0.count', 1)
        ->where('popularityReturns.1.count', 0)
        ->where('popularityReturns.2.count', 0)
    );
});

test('軸の最良 popularity=4 のとき band=mid に集計される', function () {
    // Arrange
    $user = User::factory()->create();
    // popularity=4 の馬番を軸にする（band=mid の境界値）
    createInsightsTicket(
        user: $user,
        raceDate: now()->subDays(3)->toDateString(),
        buyType: 'nagashi',
        ticketType: 'umaren',
        selections: ['axis' => [4], 'others' => [5, 6]],
        resultHorses: defaultResultHorses(),
    );

    // Act
    $response = $this->actingAs($user)->get(route('insights'));

    // Assert: popularityReturns[1] が mid（件数が 1）
    $response->assertInertia(fn ($page) => $page
        ->where('popularityReturns.1.popularity', 'mid')
        ->where('popularityReturns.1.count', 1)
        ->where('popularityReturns.0.count', 0)
        ->where('popularityReturns.2.count', 0)
    );
});

test('軸の最良 popularity=7 のとき band=low に集計される', function () {
    // Arrange
    $user = User::factory()->create();
    // popularity=7 の馬番を軸にする（band=low の境界値）
    createInsightsTicket(
        user: $user,
        raceDate: now()->subDays(3)->toDateString(),
        buyType: 'nagashi',
        ticketType: 'umaren',
        selections: ['axis' => [7], 'others' => [4, 5]],
        resultHorses: defaultResultHorses(),
    );

    // Act
    $response = $this->actingAs($user)->get(route('insights'));

    // Assert: popularityReturns[2] が low（件数が 1）
    $response->assertInertia(fn ($page) => $page
        ->where('popularityReturns.2.popularity', 'low')
        ->where('popularityReturns.2.count', 1)
        ->where('popularityReturns.0.count', 0)
        ->where('popularityReturns.1.count', 0)
    );
});
