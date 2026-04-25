<?php

use App\UseCases\TicketPurchase\ExpandSelectionsAction;

// ===== unknown ticket type / buy type =====

test('unknown ticket type returns empty array', function () {
    // Arrange
    $action = new ExpandSelectionsAction;

    // Act
    $result = $action->execute('unknown_ticket', 'single', ['horses' => [1, 2]]);

    // Assert
    expect($result)->toBe([]);
});

test('unknown buy type returns empty array', function () {
    // Arrange
    $action = new ExpandSelectionsAction;

    // Act
    $result = $action->execute('tansho', 'unknown_buy', ['horses' => [1]]);

    // Assert
    expect($result)->toBe([]);
});

// ===== tanpuku (special case) =====

describe('tanpuku', function () {
    test('single: one horse returns same horse twice as two separate picks', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('tanpuku', 'single', ['horses' => [5]]);

        // Assert: single win + single place = 2 picks for horse 5
        expect($result)->toBe([[5], [5]]);
    });

    test('box: two horses returns each horse twice', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('tanpuku', 'box', ['horses' => [3, 7]]);

        // Assert: [3],[7] merged with [3],[7]
        expect($result)->toBe([[3], [7], [3], [7]]);
    });
});

// ===== single (tansho / fukusho: horseCount=1) =====

describe('single with tansho', function () {
    test('one horse selection returns single pick', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('tansho', 'single', ['horses' => [3]]);

        // Assert
        expect($result)->toBe([[3]]);
    });

    test('multiple horse selections each become individual picks', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('tansho', 'single', ['horses' => [1, 2, 3]]);

        // Assert
        expect($result)->toBe([[1], [2], [3]]);
    });
});

describe('single with fukusho', function () {
    test('one horse selection returns single pick', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('fukusho', 'single', ['horses' => [3]]);

        // Assert
        expect($result)->toBe([[3]]);
    });

    test('multiple horse selections each become individual picks', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('fukusho', 'single', ['horses' => [1, 2, 3]]);

        // Assert
        expect($result)->toBe([[1], [2], [3]]);
    });
});

// ===== single (umaren / wide: horseCount=2 unordered) =====

describe('single with umaren', function () {
    test('exactly two horses returns one normalized pick', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'single', ['horses' => [1, 3]]);

        // Assert: sorted because unordered
        expect($result)->toBe([[1, 3]]);
    });

    test('insufficient horses returns empty array', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'single', ['horses' => [1]]);

        // Assert
        expect($result)->toBe([]);
    });

    test('null selections returns empty array', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'single', null);

        // Assert
        expect($result)->toBe([]);
    });
});

describe('single with wide', function () {
    test('exactly two horses returns one normalized pick', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('wide', 'single', ['horses' => [1, 3]]);

        // Assert: sorted because unordered
        expect($result)->toBe([[1, 3]]);
    });

    test('insufficient horses returns empty array', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('wide', 'single', ['horses' => [1]]);

        // Assert
        expect($result)->toBe([]);
    });
});

// ===== single (umatan: horseCount=2 ordered) =====

describe('single with umatan', function () {
    test('exactly two horses returns one pick with order preserved', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umatan', 'single', ['horses' => [2, 4]]);

        // Assert: ordered so no sort
        expect($result)->toBe([[2, 4]]);
    });
});

// ===== single (sanrenpuku: horseCount=3 unordered) =====

describe('single with sanrenpuku', function () {
    test('exactly three horses returns one normalized pick', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('sanrenpuku', 'single', ['horses' => [3, 1, 2]]);

        // Assert: sorted because unordered
        expect($result)->toBe([[1, 2, 3]]);
    });

    test('insufficient horses returns empty array', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('sanrenpuku', 'single', ['horses' => [1, 2]]);

        // Assert
        expect($result)->toBe([]);
    });
});

// ===== box (umaren: horseCount=2 unordered) =====

describe('box with umaren', function () {
    test('three horses produce C(3,2)=3 combinations', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'box', ['horses' => [1, 2, 3]]);

        // Assert
        expect($result)->toBe([[1, 2], [1, 3], [2, 3]]);
    });

    test('insufficient horses returns empty array', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'box', ['horses' => [1]]);

        // Assert
        expect($result)->toBe([]);
    });
});

// ===== box (umatan: horseCount=2 ordered) =====

describe('box with umatan', function () {
    test('three horses produce P(3,2)=6 permutations', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umatan', 'box', ['horses' => [1, 2, 3]]);

        // Assert: all ordered permutations
        expect($result)->toBe([[1, 2], [1, 3], [2, 1], [2, 3], [3, 1], [3, 2]]);
    });
});

// ===== box (sanrenpuku: horseCount=3 unordered) =====

describe('box with sanrenpuku', function () {
    test('four horses produce C(4,3)=4 combinations', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('sanrenpuku', 'box', ['horses' => [1, 2, 3, 4]]);

        // Assert
        expect($result)->toBe([[1, 2, 3], [1, 2, 4], [1, 3, 4], [2, 3, 4]]);
    });
});

// ===== single / box (sanrentan: horseCount=3 ordered) =====

describe('single with sanrentan', function () {
    test('three horses returns one pick with order preserved', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('sanrentan', 'single', ['horses' => [3, 1, 2]]);

        // Assert: ordered so not sorted (contrast: sanrenpuku would return [[1,2,3]])
        expect($result)->toBe([[3, 1, 2]]);
    });
});

describe('box with sanrentan', function () {
    test('three horses produce P(3,3)=6 permutations', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('sanrentan', 'box', ['horses' => [1, 2, 3]]);

        // Assert: all ordered permutations of 3 horses
        expect($result)->toBe([[1, 2, 3], [1, 3, 2], [2, 1, 3], [2, 3, 1], [3, 1, 2], [3, 2, 1]]);
    });
});

// ===== nagashi (umaren: horseCount=2) =====

describe('nagashi with umaren', function () {
    test('axis=[3] others=[1,5,7] returns three picks with axis included', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'nagashi', ['axis' => [3], 'others' => [1, 5, 7]]);

        // Assert: each combo is [axis, other], normalized (sorted) for unordered
        expect($result)->toBe([[1, 3], [3, 5], [3, 7]]);
    });

    test('empty axis returns empty array', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'nagashi', ['axis' => [], 'others' => [1, 5, 7]]);

        // Assert
        expect($result)->toBe([]);
    });

    test('empty others returns empty array', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'nagashi', ['axis' => [3], 'others' => []]);

        // Assert
        expect($result)->toBe([]);
    });
});

// ===== nagashi (sanrenpuku: horseCount=3) =====

describe('nagashi with sanrenpuku', function () {
    test('axis=[1] others=[2,3,4] returns C(3,2)=3 picks with axis', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('sanrenpuku', 'nagashi', ['axis' => [1], 'others' => [2, 3, 4]]);

        // Assert: normalized (sorted) for unordered
        expect($result)->toBe([[1, 2, 3], [1, 2, 4], [1, 3, 4]]);
    });
});

// ===== nagashi (umatan: col1/col2 format) =====

describe('nagashi with umatan col format', function () {
    test('col1=[1] col2=[2,3] returns two ordered picks', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umatan', 'nagashi', ['col1' => [1], 'col2' => [2, 3]]);

        // Assert: ordered so no sort
        expect($result)->toBe([[1, 2], [1, 3]]);
    });
});

// ===== formation (umaren: horseCount=2 unordered) =====

describe('formation with umaren', function () {
    test('two columns produce cartesian product', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'formation', ['columns' => [[1, 2], [3, 4]]]);

        // Assert: cartesian product normalized (sorted) for unordered
        expect($result)->toBe([[1, 3], [1, 4], [2, 3], [2, 4]]);
    });

    test('same horse in multiple columns is excluded from results', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'formation', ['columns' => [[1, 2], [2, 3]]]);

        // Assert: [2,2] is invalid and excluded
        expect($result)->toBe([[1, 2], [1, 3], [2, 3]]);
    });

    test('duplicate combinations are deduplicated for unordered ticket', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'formation', ['columns' => [[1, 2], [1, 2]]]);

        // Assert: [1,1] and [2,2] excluded; [1,2] and [2,1] treated as same unordered pick
        expect($result)->toBe([[1, 2]]);
    });

    test('fewer columns than horse count returns empty array', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'formation', ['columns' => [[1, 2]]]);

        // Assert: horseCount=2 requires exactly 2 columns
        expect($result)->toBe([]);
    });

    test('empty column returns empty array', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'formation', ['columns' => [[1, 2], []]]);

        // Assert
        expect($result)->toBe([]);
    });
});

// ===== formation (sanrenpuku: horseCount=3 unordered) =====

describe('formation with sanrenpuku', function () {
    test('three columns produce correct cartesian product', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('sanrenpuku', 'formation', ['columns' => [[1, 2], [3], [4, 5]]]);

        // Assert: normalized (sorted) for unordered
        expect($result)->toBe([[1, 3, 4], [1, 3, 5], [2, 3, 4], [2, 3, 5]]);
    });
});

// ===== null / empty selections edge cases =====

describe('null and empty selections edge cases', function () {
    test('null selections with tansho single returns empty array', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('tansho', 'single', null);

        // Assert
        expect($result)->toBe([]);
    });

    test('null selections with umaren nagashi returns empty array', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('umaren', 'nagashi', null);

        // Assert
        expect($result)->toBe([]);
    });

    test('string horse numbers are cast to integers', function () {
        // Arrange
        $action = new ExpandSelectionsAction;

        // Act
        $result = $action->execute('tansho', 'single', ['horses' => ['3', '5']]);

        // Assert: "3" and "5" treated as integers 3 and 5
        expect($result)->toBe([[3], [5]]);
    });
});
