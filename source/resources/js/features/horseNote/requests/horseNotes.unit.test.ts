import { describe, expect, it } from "vitest";
import { extractErrorMessage } from "./horseNotes";

describe("extractErrorMessage", () => {
	it("404 の場合は対象が存在しない旨の固定メッセージを返す", async () => {
		// Arrange
		const response = new Response(null, { status: 404 });

		// Act
		const result = await extractErrorMessage(response);

		// Assert
		expect(result).toBe(
			"対象のメモが見つかりません。すでに削除された可能性があります。",
		);
	});

	it("422 でバリデーションエラーがある場合は errors.content[0] を返す", async () => {
		// Arrange
		const response = new Response(
			JSON.stringify({
				message: "The given data was invalid.",
				errors: { content: ["メモは1000文字以内で入力してください。"] },
			}),
			{ status: 422, headers: { "Content-Type": "application/json" } },
		);

		// Act
		const result = await extractErrorMessage(response);

		// Assert
		expect(result).toBe("メモは1000文字以内で入力してください。");
	});

	it("422 で errors.content がない場合は message を返す", async () => {
		// Arrange
		const response = new Response(
			JSON.stringify({ message: "Validation failed" }),
			{ status: 422, headers: { "Content-Type": "application/json" } },
		);

		// Act
		const result = await extractErrorMessage(response);

		// Assert
		expect(result).toBe("Validation failed");
	});

	it("その他のステータスコードはステータス番号を含むフォールバック文字列を返す", async () => {
		// Arrange
		const response = new Response(null, { status: 500 });

		// Act
		const result = await extractErrorMessage(response);

		// Assert
		expect(result).toBe("Request failed: 500");
	});
});
