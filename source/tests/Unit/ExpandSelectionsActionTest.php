<?php

use App\UseCases\TicketPurchase\ExpandSelectionsAction;

// ===== unknown ticket type / buy type =====

test('未知の券種は空配列を返す', function () {
    // Arrange
    $action = new ExpandSelectionsAction;

    // Act
    $result = $action->execute('unknown_ticket', 'single', ['horses' => [1, 2]]);

    // Assert
    expect($result)->toBe([]);
});

test('未知の買い方は空配列を返す', function () {
    // Arrange
    $action = new ExpandSelectionsAction;

    // Act
    $result = $action->execute('tansho', 'unknown_buy', ['horses' => [1]]);

    // Assert
    expect($result)->toBe([]);
});

// ===== tanpuku (special case) =====

describe('tanpuku（単複）', function () {
    test('single: 1頭選択は同じ馬が2つ別の購入として返る', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('tanpuku', 'single', ['horses' => [5]]);

        // Assert: single win + single place = 2 picks for horse 5
        expect($result)->toBe([[5], [5]]);
    });

    test('box: 2頭選択は各馬が2つずつ返る', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('tanpuku', 'box', ['horses' => [3, 7]]);

        // Assert: [3],[7] merged with [3],[7]
        expect($result)->toBe([[3], [7], [3], [7]]);
    });
});

// ===== single (tansho / fukusho: horseCount=1) =====

describe('single × tansho', function () {
    test('1頭選択は1つの購入として返る', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('tansho', 'single', ['horses' => [3]]);

        // Assert
        expect($result)->toBe([[3]]);
    });

    test('複数頭選択はそれぞれ独立した購入になる', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('tansho', 'single', ['horses' => [1, 2, 3]]);

        // Assert
        expect($result)->toBe([[1], [2], [3]]);
    });
});

describe('single × fukusho', function () {
    test('1頭選択は1つの購入として返る', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('fukusho', 'single', ['horses' => [3]]);

        // Assert
        expect($result)->toBe([[3]]);
    });

    test('複数頭選択はそれぞれ独立した購入になる', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('fukusho', 'single', ['horses' => [1, 2, 3]]);

        // Assert
        expect($result)->toBe([[1], [2], [3]]);
    });
});

// ===== single (umaren / wide: horseCount=2 unordered) =====

describe('single × umaren', function () {
    test('ちょうど2頭選択で正規化された購入1つを返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'single', ['horses' => [1, 3]]);

        // Assert: sorted because unordered
        expect($result)->toBe([[1, 3]]);
    });

    test('頭数が足りないとき空配列を返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'single', ['horses' => [1]]);

        // Assert
        expect($result)->toBe([]);
    });

    test('selections が null のとき空配列を返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'single', null);

        // Assert
        expect($result)->toBe([]);
    });
});

describe('single × wide', function () {
    test('ちょうど2頭選択で正規化された購入1つを返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('wide', 'single', ['horses' => [1, 3]]);

        // Assert: sorted because unordered
        expect($result)->toBe([[1, 3]]);
    });

    test('頭数が足りないとき空配列を返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('wide', 'single', ['horses' => [1]]);

        // Assert
        expect($result)->toBe([]);
    });
});

// ===== single (umatan: horseCount=2 ordered) =====

describe('single × umatan', function () {
    test('ちょうど2頭選択で順序保持の購入1つを返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umatan', 'single', ['horses' => [2, 4]]);

        // Assert: ordered so no sort
        expect($result)->toBe([[2, 4]]);
    });
});

// ===== single (sanrenpuku: horseCount=3 unordered) =====

describe('single × sanrenpuku', function () {
    test('ちょうど3頭選択で正規化された購入1つを返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('sanrenpuku', 'single', ['horses' => [3, 1, 2]]);

        // Assert: sorted because unordered
        expect($result)->toBe([[1, 2, 3]]);
    });

    test('頭数が足りないとき空配列を返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('sanrenpuku', 'single', ['horses' => [1, 2]]);

        // Assert
        expect($result)->toBe([]);
    });
});

// ===== box (umaren: horseCount=2 unordered) =====

describe('box × umaren', function () {
    test('3頭選択で C(3,2)=3 通りの組み合わせを返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'box', ['horses' => [1, 2, 3]]);

        // Assert
        expect($result)->toBe([[1, 2], [1, 3], [2, 3]]);
    });

    test('頭数が足りないとき空配列を返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'box', ['horses' => [1]]);

        // Assert
        expect($result)->toBe([]);
    });
});

// ===== box (umatan: horseCount=2 ordered) =====

describe('box × umatan', function () {
    test('3頭選択で P(3,2)=6 通りの順列を返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umatan', 'box', ['horses' => [1, 2, 3]]);

        // Assert: all ordered permutations
        expect($result)->toBe([[1, 2], [1, 3], [2, 1], [2, 3], [3, 1], [3, 2]]);
    });
});

// ===== box (sanrenpuku: horseCount=3 unordered) =====

describe('box × sanrenpuku', function () {
    test('4頭選択で C(4,3)=4 通りの組み合わせを返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('sanrenpuku', 'box', ['horses' => [1, 2, 3, 4]]);

        // Assert
        expect($result)->toBe([[1, 2, 3], [1, 2, 4], [1, 3, 4], [2, 3, 4]]);
    });
});

// ===== single / box (sanrentan: horseCount=3 ordered) =====

describe('single × sanrentan', function () {
    test('3頭選択で順序保持の購入1つを返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('sanrentan', 'single', ['horses' => [3, 1, 2]]);

        // Assert: ordered so not sorted (contrast: sanrenpuku would return [[1,2,3]])
        expect($result)->toBe([[3, 1, 2]]);
    });
});

describe('box × sanrentan', function () {
    test('3頭選択で P(3,3)=6 通りの順列を返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('sanrentan', 'box', ['horses' => [1, 2, 3]]);

        // Assert: all ordered permutations of 3 horses
        expect($result)->toBe([[1, 2, 3], [1, 3, 2], [2, 1, 3], [2, 3, 1], [3, 1, 2], [3, 2, 1]]);
    });
});

// ===== nagashi (umaren: horseCount=2) =====

describe('nagashi × umaren', function () {
    test('axis=[3] others=[1,5,7] のとき軸を含む3つの購入を返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'nagashi', ['axis' => [3], 'others' => [1, 5, 7]]);

        // Assert: each combo is [axis, other], normalized (sorted) for unordered
        expect($result)->toBe([[1, 3], [3, 5], [3, 7]]);
    });

    test('axis が空のとき空配列を返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'nagashi', ['axis' => [], 'others' => [1, 5, 7]]);

        // Assert
        expect($result)->toBe([]);
    });

    test('others が空のとき空配列を返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'nagashi', ['axis' => [3], 'others' => []]);

        // Assert
        expect($result)->toBe([]);
    });
});

// ===== nagashi (sanrenpuku: horseCount=3) =====

describe('nagashi × sanrenpuku', function () {
    test('axis=[1] others=[2,3,4] のとき軸を含む C(3,2)=3 通りを返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('sanrenpuku', 'nagashi', ['axis' => [1], 'others' => [2, 3, 4]]);

        // Assert: normalized (sorted) for unordered
        expect($result)->toBe([[1, 2, 3], [1, 2, 4], [1, 3, 4]]);
    });
});

// ===== nagashi (umatan: col1/col2 format) =====

describe('nagashi × umatan（col1/col2 形式）', function () {
    test('col1=[1] col2=[2,3] のとき順序保持の2つの購入を返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umatan', 'nagashi', ['col1' => [1], 'col2' => [2, 3]]);

        // Assert: ordered so no sort
        expect($result)->toBe([[1, 2], [1, 3]]);
    });
});

// ===== formation (umaren: horseCount=2 unordered) =====

describe('formation × umaren', function () {
    test('2列の直積を返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'formation', ['columns' => [[1, 2], [3, 4]]]);

        // Assert: cartesian product normalized (sorted) for unordered
        expect($result)->toBe([[1, 3], [1, 4], [2, 3], [2, 4]]);
    });

    test('複数列に同じ馬がいる場合は重複馬の組み合わせを除外する', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'formation', ['columns' => [[1, 2], [2, 3]]]);

        // Assert: [2,2] is invalid and excluded
        expect($result)->toBe([[1, 2], [1, 3], [2, 3]]);
    });

    test('順不同券種では重複した組み合わせを排除する', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'formation', ['columns' => [[1, 2], [1, 2]]]);

        // Assert: [1,1] and [2,2] excluded; [1,2] and [2,1] treated as same unordered pick
        expect($result)->toBe([[1, 2]]);
    });

    test('列数が必要頭数より少ないとき空配列を返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'formation', ['columns' => [[1, 2]]]);

        // Assert: horseCount=2 requires exactly 2 columns
        expect($result)->toBe([]);
    });

    test('空の列があるとき空配列を返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'formation', ['columns' => [[1, 2], []]]);

        // Assert
        expect($result)->toBe([]);
    });
});

// ===== formation (sanrenpuku: horseCount=3 unordered) =====

describe('formation × sanrenpuku', function () {
    test('3列の直積が正しく計算される', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('sanrenpuku', 'formation', ['columns' => [[1, 2], [3], [4, 5]]]);

        // Assert: normalized (sorted) for unordered
        expect($result)->toBe([[1, 3, 4], [1, 3, 5], [2, 3, 4], [2, 3, 5]]);
    });
});

// ===== null / empty selections edge cases =====

describe('null・空 selections のエッジケース', function () {
    test('単勝 single で selections が null のとき空配列を返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('tansho', 'single', null);

        // Assert
        expect($result)->toBe([]);
    });

    test('馬連 nagashi で selections が null のとき空配列を返す', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'nagashi', null);

        // Assert
        expect($result)->toBe([]);
    });

    test('文字列の馬番が整数にキャストされる', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('tansho', 'single', ['horses' => ['3', '5']]);

        // Assert: "3" and "5" treated as integers 3 and 5
        expect($result)->toBe([[3], [5]]);
    });
});
