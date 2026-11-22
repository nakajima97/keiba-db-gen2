/**
 * <meta name="csrf-token"> から CSRF トークンを取り出す。
 * テスト等で meta タグが存在しない場合は空文字列を返す。
 */
export const getCsrfToken = (): string => {
	if (typeof document === "undefined") {
		return "";
	}
	const meta = document.querySelector<HTMLMetaElement>(
		'meta[name="csrf-token"]',
	);
	return meta?.content ?? "";
};

/**
 * 認証付き JSON リクエストの共通ヘッダ。
 */
export const buildJsonHeaders = (): HeadersInit => {
	return {
		"Content-Type": "application/json",
		Accept: "application/json",
		"X-Requested-With": "XMLHttpRequest",
		"X-CSRF-TOKEN": getCsrfToken(),
	};
};
