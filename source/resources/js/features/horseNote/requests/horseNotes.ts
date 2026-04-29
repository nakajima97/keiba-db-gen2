import type { HorseNote } from "../types/horseNote";
import { buildJsonHeaders } from "./csrf";

type ApiResponse = {
	data: HorseNote;
};

type ApiListResponse = {
	data: HorseNote[];
};

type ValidationErrorBody = {
	message?: string;
	errors?: {
		content?: string[];
	};
};

/**
 * fetch レスポンスのエラー時に投げるエラー。
 * 422 のときは API が返す `errors.content[0]` または `message` を `message` に設定する。
 * `status` プロパティを持たせ、上位で参照可能にする。
 */
export class HorseNoteRequestError extends Error {
	public readonly status: number;

	constructor(message: string, status: number) {
		super(message);
		this.name = "HorseNoteRequestError";
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

/**
 * 指定した競走馬のメモ一覧を取得する。
 */
export const listNotes = async (horseId: number): Promise<HorseNote[]> => {
	const response = await fetch(`/api/horses/${horseId}/notes`, {
		method: "GET",
		headers: buildJsonHeaders(),
		credentials: "same-origin",
	});
	if (!response.ok) {
		const message = await extractErrorMessage(response);
		throw new HorseNoteRequestError(message, response.status);
	}
	const json = (await response.json()) as ApiListResponse;
	return json.data;
};

/**
 * メモを新規作成する。raceId が null の場合はレース紐づきなしのメモを作成する。
 */
export const createNote = async (
	horseId: number,
	raceId: number | null,
	content: string,
): Promise<HorseNote> => {
	const response = await fetch(`/api/horses/${horseId}/notes`, {
		method: "POST",
		headers: buildJsonHeaders(),
		credentials: "same-origin",
		body: JSON.stringify({ race_id: raceId, content }),
	});
	if (!response.ok) {
		const message = await extractErrorMessage(response);
		throw new HorseNoteRequestError(message, response.status);
	}
	const json = (await response.json()) as ApiResponse;
	return json.data;
};

/**
 * 既存メモの本文を更新する。
 */
export const updateNote = async (
	noteId: number,
	content: string,
): Promise<HorseNote> => {
	const response = await fetch(`/api/horse-notes/${noteId}`, {
		method: "PUT",
		headers: buildJsonHeaders(),
		credentials: "same-origin",
		body: JSON.stringify({ content }),
	});
	if (!response.ok) {
		const message = await extractErrorMessage(response);
		throw new HorseNoteRequestError(message, response.status);
	}
	const json = (await response.json()) as ApiResponse;
	return json.data;
};

/**
 * 既存メモを削除する。成功時は 204 No Content を返すためレスポンスボディは扱わない。
 */
export const deleteNote = async (noteId: number): Promise<void> => {
	const response = await fetch(`/api/horse-notes/${noteId}`, {
		method: "DELETE",
		headers: buildJsonHeaders(),
		credentials: "same-origin",
	});
	if (!response.ok) {
		const message = await extractErrorMessage(response);
		throw new HorseNoteRequestError(message, response.status);
	}
};
