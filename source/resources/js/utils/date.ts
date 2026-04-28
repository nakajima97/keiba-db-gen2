/**
 * ハイフン区切りの日付文字列を、表示用のスラッシュ区切りに変換する。
 *
 * バックエンドから受け取る日付は ISO 8601 準拠の `YYYY-MM-DD` 形式（例: `"2026-04-25"`）だが、
 * 画面表示では国内の慣習に合わせた `YYYY/MM/DD` 形式（例: `"2026/04/25"`）を用いる。
 *
 * @param date - `YYYY-MM-DD` 形式の日付文字列。フォーマット検証は行わないため、
 *               想定外の形式が渡された場合はハイフンのみがスラッシュに置換される。
 * @returns `YYYY/MM/DD` 形式に変換された日付文字列。
 */
export const formatDateDisplay = (date: string): string => {
	return date.replace(/-/g, "/");
};
