<?php

namespace App\Enums;

/**
 * 馬券種別の name (ticket_types.name と同値)。
 *
 * `tanpuku` は本アプリ独自の概念（単勝+複勝の同時購入）であり、
 * JRA 公式券種ではないため `App\UseCases\RaceResult\StoreAction::TICKET_TYPE_MAP` には含まれない。
 *
 * @see resources/js/constants/ticketType.ts と同期させること
 */
enum TicketTypeName: string
{
    case Tansho = 'tansho';
    case Fukusho = 'fukusho';
    case Wakuren = 'wakuren';
    case Umaren = 'umaren';
    case Umatan = 'umatan';
    case Wide = 'wide';
    case Sanrenpuku = 'sanrenpuku';
    case Sanrentan = 'sanrentan';
    case Tanpuku = 'tanpuku';

    /**
     * 着順を保持する券種か。
     */
    public function isOrdered(): bool
    {
        return match ($this) {
            self::Umatan, self::Sanrentan => true,
            default => false,
        };
    }

    /**
     * 照合対象となる馬番の数。
     */
    public function horseCount(): int
    {
        return match ($this) {
            self::Tansho, self::Fukusho => 1,
            self::Sanrenpuku, self::Sanrentan => 3,
            self::Wakuren, self::Umaren, self::Umatan, self::Wide, self::Tanpuku => 2,
        };
    }
}
