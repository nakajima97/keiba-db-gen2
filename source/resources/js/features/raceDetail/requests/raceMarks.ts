import type {
	MarkValue,
	RaceMarkValue,
} from "../presentational/RaceDetail/types";
import { buildJsonHeaders } from "./csrf";

type UpsertResponse = {
	data: RaceMarkValue;
};

/**
 * 印を upsert する。markValue が null の場合は削除（API は 204 を返す）。
 * 削除時は null を、それ以外は API レスポンスの値を返す。
 */
export async function upsertMark(
	raceUid: string,
	columnId: number,
	raceEntryId: number,
	markValue: MarkValue | null,
): Promise<RaceMarkValue | null> {
	const body = JSON.stringify({ mark_value: markValue ?? "" });
	const response = await fetch(
		`/api/races/${raceUid}/mark-columns/${columnId}/entries/${raceEntryId}/mark`,
		{
			method: "PUT",
			headers: buildJsonHeaders(),
			credentials: "same-origin",
			body,
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
