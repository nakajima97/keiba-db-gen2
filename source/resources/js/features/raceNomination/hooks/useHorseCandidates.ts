import { useEffect, useState } from "react";
import { searchHorses } from "../requests/raceNominations";
import type { HorseCandidate } from "../types/raceNomination";

const DEBOUNCE_MS = 300;

/**
 * 入力中の馬名から競走馬の候補を取得する。入力が止まってから DEBOUNCE_MS 後に検索し、
 * 古いリクエストは中断する。空白のみの入力では候補を空にする。
 */
export const useHorseCandidates = (keyword: string): HorseCandidate[] => {
	const [candidates, setCandidates] = useState<HorseCandidate[]>([]);

	useEffect(() => {
		const trimmed = keyword.trim();
		if (trimmed === "") {
			setCandidates([]);
			return;
		}

		const controller = new AbortController();
		const timer = setTimeout(() => {
			searchHorses(trimmed, controller.signal)
				.then(setCandidates)
				.catch(() => {
					// 候補表示は補助機能のため、失敗しても入力は続けられるよう握りつぶす
				});
		}, DEBOUNCE_MS);

		return () => {
			clearTimeout(timer);
			controller.abort();
		};
	}, [keyword]);

	return candidates;
};
