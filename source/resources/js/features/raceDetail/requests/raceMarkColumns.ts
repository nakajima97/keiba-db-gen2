import type { RaceMarkColumn } from "../presentational/RaceDetail/types";
import { buildJsonHeaders } from "./csrf";

type ApiResponse = {
	data: RaceMarkColumn;
};

async function ensureOk(response: Response): Promise<void> {
	if (!response.ok) {
		throw new Error(`Request failed: ${response.status}`);
	}
}

export async function createOtherColumn(
	raceUid: string,
	label: string,
): Promise<RaceMarkColumn> {
	const response = await fetch(`/api/races/${raceUid}/mark-columns`, {
		method: "POST",
		headers: buildJsonHeaders(),
		credentials: "same-origin",
		body: JSON.stringify({ label }),
	});
	await ensureOk(response);
	const json = (await response.json()) as ApiResponse;
	return json.data;
}

export async function updateColumnLabel(
	raceUid: string,
	columnId: number,
	label: string,
): Promise<RaceMarkColumn> {
	const response = await fetch(
		`/api/races/${raceUid}/mark-columns/${columnId}`,
		{
			method: "PATCH",
			headers: buildJsonHeaders(),
			credentials: "same-origin",
			body: JSON.stringify({ label }),
		},
	);
	await ensureOk(response);
	const json = (await response.json()) as ApiResponse;
	return json.data;
}

export async function deleteColumn(
	raceUid: string,
	columnId: number,
): Promise<void> {
	const response = await fetch(
		`/api/races/${raceUid}/mark-columns/${columnId}`,
		{
			method: "DELETE",
			headers: buildJsonHeaders(),
			credentials: "same-origin",
		},
	);
	await ensureOk(response);
}
