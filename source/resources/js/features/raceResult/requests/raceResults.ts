import { buildJsonHeaders } from "@/features/raceDetail/requests/csrf";
import { destroy as raceResultDestroy } from "@/routes/races/result";

type ErrorBody = {
	message?: string;
};

const GENERIC_ERROR_MESSAGE =
	"レース結果の削除に失敗しました。時間をおいて再度お試しください。";

const extractErrorMessage = async (response: Response): Promise<string> => {
	if (response.status === 409) {
		try {
			const json = (await response.json()) as ErrorBody;
			if (json.message != null && json.message !== "") {
				return json.message;
			}
		} catch (_e) {
			// JSON parse 失敗時はフォールバック
		}
	}
	return GENERIC_ERROR_MESSAGE;
};

/**
 * 指定レースの結果（着順・払戻）を削除する。成功時は 200。
 */
export const deleteRaceResult = async (raceUid: string): Promise<void> => {
	const response = await fetch(raceResultDestroy.url(raceUid), {
		method: "DELETE",
		headers: buildJsonHeaders(),
		credentials: "same-origin",
	});

	if (!response.ok) {
		const message = await extractErrorMessage(response);
		throw new Error(message);
	}
};
