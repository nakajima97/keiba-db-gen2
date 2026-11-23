import { buildJsonHeaders } from "@/features/raceDetail/requests/csrf";
import type {
	CommentOutcome,
	CommentSentiment,
	CommentSentimentStats,
	CommentSpeakerType,
	EntryComment,
} from "../types";

/** 更新系レスポンスの stats（当該出走の調教師・騎手それぞれの的中率） */
export type EntryCommentStats = {
	trainer_id: number | null;
	trainer: CommentSentimentStats | null;
	jockey_id: number;
	jockey: CommentSentimentStats;
};

export type EntryCommentPayload = {
	speaker_type: CommentSpeakerType;
	sentiment: CommentSentiment;
	content: string;
	outcome: CommentOutcome | null;
};

type MutationResponse = {
	data: EntryComment;
	stats: EntryCommentStats;
};

type ValidationErrorBody = {
	message?: string;
	errors?: Record<string, string[]>;
};

/**
 * 調教師・騎手コメント API のエラー。422 のときは最初のバリデーションメッセージを message に設定する。
 */
export class EntryCommentRequestError extends Error {
	public readonly status: number;

	constructor(message: string, status: number) {
		super(message);
		this.name = "EntryCommentRequestError";
		this.status = status;
	}
}

const extractErrorMessage = async (response: Response): Promise<string> => {
	if (response.status === 422) {
		try {
			const json = (await response.json()) as ValidationErrorBody;
			const firstError = Object.values(json.errors ?? {})[0]?.[0];
			if (firstError != null) {
				return firstError;
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

const request = async <T>(
	url: string,
	method: "POST" | "PATCH" | "DELETE",
	body?: unknown,
): Promise<T> => {
	const response = await fetch(url, {
		method,
		headers: buildJsonHeaders(),
		credentials: "same-origin",
		body: body !== undefined ? JSON.stringify(body) : undefined,
	});
	if (!response.ok) {
		const message = await extractErrorMessage(response);
		throw new EntryCommentRequestError(message, response.status);
	}
	return (await response.json()) as T;
};

const commentsUrl = (raceUid: string, raceEntryId: number) =>
	`/api/races/${raceUid}/entries/${raceEntryId}/comments`;

const commentUrl = (
	raceUid: string,
	raceEntryId: number,
	comment: Pick<EntryComment, "speaker_type" | "id">,
) =>
	`${commentsUrl(raceUid, raceEntryId)}/${comment.speaker_type}/${comment.id}`;

export const createEntryComment = (
	raceUid: string,
	raceEntryId: number,
	payload: EntryCommentPayload,
): Promise<MutationResponse> =>
	request<MutationResponse>(commentsUrl(raceUid, raceEntryId), "POST", payload);

/**
 * コメントを部分更新する。speaker_type を変えるとコメントは別 ID で作り直される。
 */
export const updateEntryComment = (
	raceUid: string,
	raceEntryId: number,
	comment: Pick<EntryComment, "speaker_type" | "id">,
	payload: Partial<EntryCommentPayload>,
): Promise<MutationResponse> =>
	request<MutationResponse>(
		commentUrl(raceUid, raceEntryId, comment),
		"PATCH",
		payload,
	);

export const deleteEntryComment = async (
	raceUid: string,
	raceEntryId: number,
	comment: Pick<EntryComment, "speaker_type" | "id">,
): Promise<EntryCommentStats> => {
	const json = await request<{ stats: EntryCommentStats }>(
		commentUrl(raceUid, raceEntryId, comment),
		"DELETE",
	);
	return json.stats;
};
