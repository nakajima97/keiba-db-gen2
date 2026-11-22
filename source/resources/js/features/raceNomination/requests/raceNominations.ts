import type {
	HorseCandidate,
	RaceNomination,
	RaceNominationStatus,
} from "../types/raceNomination";
import { buildJsonHeaders } from "./csrf";

type ValidationErrorBody = {
	message?: string;
	errors?: Record<string, string[] | undefined>;
};

/**
 * 登録馬 API のエラー。422 のときは API のバリデーションメッセージを message に設定する。
 */
export class RaceNominationRequestError extends Error {
	public readonly status: number;

	constructor(message: string, status: number) {
		super(message);
		this.name = "RaceNominationRequestError";
		this.status = status;
	}
}

const extractErrorMessage = async (response: Response): Promise<string> => {
	if (response.status === 422) {
		try {
			const json = (await response.json()) as ValidationErrorBody;
			const fieldError =
				json.errors?.horse_name?.[0] ?? json.errors?.horse_id?.[0];
			if (fieldError != null) {
				return fieldError;
			}
			if (json.message != null) {
				return json.message;
			}
		} catch (_e) {
			// JSON parse 失敗時はフォールバック
		}
	}
	return "登録馬の追加に失敗しました";
};

/**
 * 登録馬を 1 頭追加する。horseId を指定すると候補から選んだ競走馬を、省略すると horseName で照合・新規作成する。
 */
export async function createNomination(
	raceUid: string,
	input: { horseId: number } | { horseName: string },
): Promise<RaceNomination> {
	const body =
		"horseId" in input
			? { horse_id: input.horseId }
			: { horse_name: input.horseName };
	const response = await fetch(`/api/races/${raceUid}/nominations`, {
		method: "POST",
		headers: buildJsonHeaders(),
		credentials: "same-origin",
		body: JSON.stringify(body),
	});
	if (!response.ok) {
		throw new RaceNominationRequestError(
			await extractErrorMessage(response),
			response.status,
		);
	}
	const json = (await response.json()) as { data: RaceNomination };
	return json.data;
}

/**
 * 登録馬を削除する。
 */
export async function deleteNomination(
	raceUid: string,
	nominationId: number,
): Promise<void> {
	const response = await fetch(
		`/api/races/${raceUid}/nominations/${nominationId}`,
		{
			method: "DELETE",
			headers: buildJsonHeaders(),
			credentials: "same-origin",
		},
	);
	if (!response.ok) {
		throw new RaceNominationRequestError(
			`Request failed: ${response.status}`,
			response.status,
		);
	}
}

/**
 * 登録馬のチェック／消しを設定する。status が null の場合は解除する（API は 204 を返す）。
 */
export async function upsertNominationMark(
	raceUid: string,
	nominationId: number,
	status: RaceNominationStatus | null,
): Promise<void> {
	const response = await fetch(
		`/api/races/${raceUid}/nominations/${nominationId}/mark`,
		{
			method: "PUT",
			headers: buildJsonHeaders(),
			credentials: "same-origin",
			body: JSON.stringify({ status: status ?? "" }),
		},
	);
	if (!response.ok) {
		throw new RaceNominationRequestError(
			`Request failed: ${response.status}`,
			response.status,
		);
	}
}

/**
 * 馬名の部分一致で競走馬の候補を検索する。
 */
export async function searchHorses(
	keyword: string,
	signal?: AbortSignal,
): Promise<HorseCandidate[]> {
	const params = new URLSearchParams({ keyword });
	const response = await fetch(`/api/horses/search?${params.toString()}`, {
		method: "GET",
		headers: buildJsonHeaders(),
		credentials: "same-origin",
		signal,
	});
	if (!response.ok) {
		throw new RaceNominationRequestError(
			`Request failed: ${response.status}`,
			response.status,
		);
	}
	const json = (await response.json()) as { data: HorseCandidate[] };
	return json.data;
}
