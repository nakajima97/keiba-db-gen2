<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * @return array{userId: int, ticketPurchaseId: int}
 */
function createTicketPurchaseForDestroyTest(int $userId): array
{
    $now = now();

    $venueId = DB::table('venues')->insertGetId([
        'name' => '東京',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

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
        'uid' => 'test-uid-'.uniqid(),
        'venue_id' => $venueId,
        'race_date' => '2026-04-05',
        'race_number' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $ticketPurchaseId = DB::table('ticket_purchases')->insertGetId([
        'user_id' => $userId,
        'race_id' => $raceId,
        'ticket_type_id' => $ticketTypeId,
        'buy_type_id' => $buyTypeId,
        'selections' => json_encode(['axis' => [1], 'others' => [2, 3]]),
        'unit_stake' => 100,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    return ['userId' => $userId, 'ticketPurchaseId' => $ticketPurchaseId];
}

test('認証済みユーザーは自分の馬券購入を削除できる', function () {
    // Arrange
    $user = User::factory()->create();
    ['ticketPurchaseId' => $ticketPurchaseId] = createTicketPurchaseForDestroyTest($user->id);

    // Act
    $response = $this->actingAs($user)->delete(route('tickets.destroy', $ticketPurchaseId));

    // Assert
    $response->assertRedirect(route('tickets.index'));
    $this->assertDatabaseMissing('ticket_purchases', [
        'id' => $ticketPurchaseId,
    ]);
});

test('他ユーザーの馬券購入を削除しようとすると403が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    ['ticketPurchaseId' => $ticketPurchaseId] = createTicketPurchaseForDestroyTest($otherUser->id);

    // Act
    $response = $this->actingAs($user)->delete(route('tickets.destroy', $ticketPurchaseId));

    // Assert
    $response->assertForbidden();
});

test('未認証ユーザーが馬券購入を削除しようとするとログイン画面にリダイレクトされる', function () {
    // Arrange
    $user = User::factory()->create();
    ['ticketPurchaseId' => $ticketPurchaseId] = createTicketPurchaseForDestroyTest($user->id);

    // Act
    $response = $this->delete(route('tickets.destroy', $ticketPurchaseId));

    // Assert
    $response->assertRedirect(route('login'));
});
