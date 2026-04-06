<?php

test('returns a successful response', function () {
    // Act
    $response = $this->get(route('home'));

    // Assert
    $response->assertOk();
});
