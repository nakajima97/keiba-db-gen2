import { buildJsonHeaders } from "@/features/raceDetail/requests/csrf";

export type ApiTrainerNote = {
	id: number;
	trainer_id: number;
	content: string;
	created_at: string | null;
	updated_at: string | null;
};

type ApiResponse = {
	data: ApiTrainerNote;
};

type ValidationErrorBody = {
	message?: string;
	errors?: {
		content?: string[];
	};
};

/**
 * 調教師メモ API のエラーを表す例外。
 * 422 のときは API が返す `errors.content[0]` または `message` を `message` に設定し、
 * `status` プロパティで HTTP ステータスを参照可能にする。
 */
export class TrainerNoteRequestError extends Error {
	public readonly status: number;

	constructor(message: string, status: number) {
		super(message);
		this.name = "TrainerNoteRequestError";
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

const noteUrl = (trainerId: number) => `/api/trainers/${trainerId}/note`;

/**
 * 調教師メモを upsert する。新規作成は 201、更新は 200。
 */
export const upsertTrainerNote = async (
	trainerId: number,
	content: string,
): Promise<ApiTrainerNote> => {
	const response = await fetch(noteUrl(trainerId), {
		method: "PUT",
		headers: buildJsonHeaders(),
		credentials: "same-origin",
		body: JSON.stringify({ content }),
	});
	if (!response.ok) {
		const message = await extractErrorMessage(response);
		throw new TrainerNoteRequestError(message, response.status);
	}
	const json = (await response.json()) as ApiResponse;
	return json.data;
};

/**
 * 調教師メモを削除する。成功時は 204。
 */
export const deleteTrainerNote = async (trainerId: number): Promise<void> => {
	const response = await fetch(noteUrl(trainerId), {
		method: "DELETE",
		headers: buildJsonHeaders(),
		credentials: "same-origin",
	});
	if (!response.ok) {
		const message = await extractErrorMessage(response);
		throw new TrainerNoteRequestError(message, response.status);
	}
};
