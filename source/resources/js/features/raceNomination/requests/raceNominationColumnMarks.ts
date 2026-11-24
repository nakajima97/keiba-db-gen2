import type { MarkValue } from "@/features/raceDetail/presentational/RaceDetail/types";
import type { RaceNominationColumnMarkValue } from "../types/raceNomination";
import { buildJsonHeaders } from "./csrf";

type UpsertResponse = {
	data: RaceNominationColumnMarkValue;
};

/**
 * 登録馬の他人の印を upsert する。markValue が null の場合は解除（API は 204 を返す）。
 * 解除時は null を、それ以外は API レスポンスの値を返す。
 */
export async function upsertNominationColumnMark(
	raceUid: string,
	columnId: number,
	raceNominationId: number,
	markValue: MarkValue | null,
): Promise<RaceNominationColumnMarkValue | null> {
	const response = await fetch(
		`/api/races/${raceUid}/mark-columns/${columnId}/nominations/${raceNominationId}/mark`,
		{
			method: "PUT",
			headers: buildJsonHeaders(),
			credentials: "same-origin",
			body: JSON.stringify({ mark_value: markValue ?? "" }),
		},
	);
	if (!response.ok) {
		throw new Error(`Request failed: ${response.status}`);
	}
	if (response.status === 204) {
		return null;
	}
	const json = (await response.json()) as UpsertResponse;
	return json.data;
}
