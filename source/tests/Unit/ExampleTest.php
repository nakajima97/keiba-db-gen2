<?php

test('ホームへのGETリクエストが成功レスポンスを返す', function () {
    // Act
    $response = $this->get(route('home'));

    // Assert
    $response->assertOk();
});
