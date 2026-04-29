import { buildJsonHeaders } from "@/features/raceDetail/requests/csrf";

type ApiMemo = {
	id: number;
	race_mark_column_id: number;
	race_entry_id: number;
	content: string;
	created_at: string | null;
	updated_at: string | null;
};

type ApiResponse = {
	data: ApiMemo;
};

type ValidationErrorBody = {
	message?: string;
	errors?: {
		content?: string[];
	};
};

/**
 * 印メモ API のエラーを表す例外。
 * 422 のときは API が返す `errors.content[0]` または `message` を `message` に設定し、
 * `status` プロパティで HTTP ステータスを参照可能にする。
 */
export class RaceMarkMemoRequestError extends Error {
	public readonly status: number;

	constructor(message: string, status: number) {
		super(message);
		this.name = "RaceMarkMemoRequestError";
		this.status = status;
	}
}

const extractErrorMessage = async (response: Response): Promise<string> => {
	if (response.status === 422) {
		try {
			const json = (await response.json()) as ValidationErrorBody;
			const contentError = json.errors?.content?.[0];
			if (contentError != null) {
				return contentError;
			}
			if (json.message != null) {
				return json.message;
			}
		} catch (_e) {
			// JSON parse 失敗時はフォールバック
		}
	}
	return `Request failed: ${response.status}`;
};

const memoUrl = (raceUid: string, columnId: number, raceEntryId: number) =>
	`/api/races/${raceUid}/mark-columns/${columnId}/entries/${raceEntryId}/memo`;

/**
 * 印メモを upsert する。新規作成は 201、更新は 200。
 */
export const upsertMemo = async (
	raceUid: string,
	columnId: number,
	raceEntryId: number,
	content: string,
): Promise<ApiMemo> => {
	const response = await fetch(memoUrl(raceUid, columnId, raceEntryId), {
		method: "PUT",
		headers: buildJsonHeaders(),
		credentials: "same-origin",
		body: JSON.stringify({ content }),
	});
	if (!response.ok) {
		const message = await extractErrorMessage(response);
		throw new RaceMarkMemoRequestError(message, response.status);
	}
	const json = (await response.json()) as ApiResponse;
	return json.data;
};

/**
 * 印メモを削除する。成功時は 204。
 */
export const deleteMemo = async (
	raceUid: string,
	columnId: number,
	raceEntryId: number,
): Promise<void> => {
	const response = await fetch(memoUrl(raceUid, columnId, raceEntryId), {
		method: "DELETE",
		headers: buildJsonHeaders(),
		credentials: "same-origin",
	});
	if (!response.ok) {
		const message = await extractErrorMessage(response);
		throw new RaceMarkMemoRequestError(message, response.status);
	}
};
