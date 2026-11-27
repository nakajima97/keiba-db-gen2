import type { RaceNominationMarkColumn } from "../types/raceNomination";
import { buildJsonHeaders } from "./csrf";

type ApiResponse = {
	data: RaceNominationMarkColumn;
};

const columnsUrl = (raceUid: string): string =>
	`/api/races/${raceUid}/nomination-mark-columns`;

async function ensureOk(response: Response): Promise<void> {
	if (!response.ok) {
		throw new Error(`Request failed: ${response.status}`);
	}
}

/**
 * 登録馬用の他人の印列を追加する。
 */
export async function createNominationMarkColumn(
	raceUid: string,
	label: string,
): Promise<RaceNominationMarkColumn> {
	const response = await fetch(columnsUrl(raceUid), {
		method: "POST",
		headers: buildJsonHeaders(),
		credentials: "same-origin",
		body: JSON.stringify({ label }),
	});
	await ensureOk(response);
	const json = (await response.json()) as ApiResponse;
	return json.data;
}

/**
 * 登録馬用の他人の印列のラベルを更新する。
 */
export async function updateNominationMarkColumnLabel(
	raceUid: string,
	columnId: number,
	label: string,
): Promise<RaceNominationMarkColumn> {
	const response = await fetch(`${columnsUrl(raceUid)}/${columnId}`, {
		method: "PATCH",
		headers: buildJsonHeaders(),
		credentials: "same-origin",
		body: JSON.stringify({ label }),
	});
	await ensureOk(response);
	const json = (await response.json()) as ApiResponse;
	return json.data;
}

/**
 * 登録馬用の他人の印列を削除する。列の印・印メモもサーバ側で削除される。
 */
export async function deleteNominationMarkColumn(
	raceUid: string,
	columnId: number,
): Promise<void> {
	const response = await fetch(`${columnsUrl(raceUid)}/${columnId}`, {
		method: "DELETE",
		headers: buildJsonHeaders(),
		credentials: "same-origin",
	});
	await ensureOk(response);
}
