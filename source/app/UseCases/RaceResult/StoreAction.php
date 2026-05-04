<?php

namespace App\UseCases\RaceResult;

use App\Enums\TicketTypeName;
use App\Exceptions\RaceResult\ParseException;
use App\Models\Horse;
use App\Models\Jockey;
use App\Models\Race;
use App\Models\RacePayout;
use App\Models\RacePayoutHorse;
use App\Models\RaceResultHorse;
use App\Models\TicketPurchase;
use App\Models\TicketType;
use App\Services\RaceResultHorseParser;
use App\Services\RaceResultPayoutParser;
use App\UseCases\TicketPurchase\CalculatePayoutAmountAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * パース済みのレース結果テキストおよび払戻テキストから、
 * race_result_horses / race_payouts / race_payout_horses を保存する。
 *
 * 払戻テキスト・着順テキストのパースは
 * {@see RaceResultPayoutParser} / {@see RaceResultHorseParser} に委譲する。
 *
 * @throws \InvalidArgumentException パースに失敗した場合
 */
class StoreAction
{
    public function __construct(
        private readonly CalculatePayoutAmountAction $calculatePayoutAmountAction,
        private readonly RaceResultHorseParser $raceResultHorseParser,
        private readonly RaceResultPayoutParser $raceResultPayoutParser,
    ) {}

    /**
     * @param  array{text: string, result_text: string}  $data
     */
    public function execute(array $data, string $uid, int $userId): void
    {
        $race = Race::where('uid', $uid)->firstOrFail();
        $raceId = $race->id;

        try {
            $resultHorseEntries = $this->raceResultHorseParser->parse($data['result_text']);
        } catch (\InvalidArgumentException $e) {
            throw new ParseException($e->getMessage(), 'result_text');
        }

        try {
            $entries = $this->raceResultPayoutParser->parse($data['text']);
            $this->raceResultPayoutParser->validateAllTypesPresent($entries);
        } catch (\InvalidArgumentException $e) {
            throw new ParseException($e->getMessage(), 'text');
        }

        $ticketTypeNames = array_unique(array_column($entries, 'ticket_type'));
        $ticketTypeIds = TicketType::whereIn('name', $ticketTypeNames)
            ->pluck('id', 'name')
            ->all();

        DB::transaction(function () use ($entries, $resultHorseEntries, $raceId, $ticketTypeIds, $userId): void {
            foreach ($resultHorseEntries as $horseEntry) {
                $horse = Horse::firstOrCreate(['name' => $horseEntry['horse_name']]);
                $jockey = Jockey::firstOrCreate(['name' => $horseEntry['jockey_name']]);
                RaceResultHorse::create(array_merge(
                    ['race_id' => $raceId, 'horse_id' => $horse->id, 'jockey_id' => $jockey->id],
                    $horseEntry
                ));
            }

            foreach ($entries as $entry) {
                $ticketTypeId = $ticketTypeIds[$entry['ticket_type']];

                $payout = RacePayout::create([
                    'race_id' => $raceId,
                    'ticket_type_id' => $ticketTypeId,
                    'payout_amount' => $entry['amount'],
                    'popularity' => $entry['popularity'],
                ]);

                $isOrdered = TicketTypeName::from($entry['ticket_type'])->isOrdered();
                $horseNumbers = $entry['horse_numbers'];

                if (! $isOrdered) {
                    sort($horseNumbers);
                }

                foreach ($horseNumbers as $index => $horseNumber) {
                    RacePayoutHorse::create([
                        'race_payout_id' => $payout->id,
                        'horse_number' => $horseNumber,
                        'sort_order' => $index + 1,
                    ]);
                }
            }

            $this->updateTicketPurchasesPayoutAmount($raceId, $userId);
        });
    }

    /**
     * 対象レースの購入馬券（投稿ユーザーのみ）を取得し、レース結果と照合して payout_amount を更新する。
     */
    private function updateTicketPurchasesPayoutAmount(int $raceId, int $userId): void
    {
        /** @var Collection<int, TicketPurchase> $purchases */
        $purchases = TicketPurchase::with(['ticketType', 'buyType'])
            ->where('race_id', $raceId)
            ->where('user_id', $userId)
            ->get();

        foreach ($purchases as $purchase) {
            $payoutAmount = $this->calculatePayoutAmountAction->execute($purchase);
            if ($payoutAmount !== null) {
                $purchase->payout_amount = $payoutAmount;
                $purchase->save();
            }
        }
    }
}
