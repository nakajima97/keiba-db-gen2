<?php

use App\Models\Horse;
use App\Models\User;

// ===== GET /api/horses/search =====

test('馬名の部分一致で競走馬の候補を返す', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = Horse::create(['name' => 'サクラケンサク', 'birth_year' => 2022]);
    Horse::create(['name' => 'ベツノウマ', 'birth_year' => 2021]);

    // Act
    $response = $this->actingAs($user)->getJson('/api/horses/search?keyword=ケンサク');

    // Assert
    $response->assertOk();
    $response->assertExactJson([
        'data' => [
            ['id' => $horse->id, 'name' => 'サクラケンサク', 'birth_year' => 2022],
        ],
    ]);
});
