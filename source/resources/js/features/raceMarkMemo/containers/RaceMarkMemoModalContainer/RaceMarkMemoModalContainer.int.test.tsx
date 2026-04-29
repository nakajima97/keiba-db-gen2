import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import RaceMarkMemoModalContainer from "./index";

const buildResponse = (status: number, body: unknown) =>
	new Response(body == null ? null : JSON.stringify(body), {
		status,
		headers: { "Content-Type": "application/json" },
	});

describe("RaceMarkMemoModalContainer", () => {
	afterEach(() => {
		vi.restoreAllMocks();
	});

	describe("create モード", () => {
		beforeEach(() => {
			vi.spyOn(global, "fetch").mockResolvedValue(
				buildResponse(201, {
					data: {
						id: 1,
						race_mark_column_id: 101,
						race_entry_id: 5,
						content: "新規メモ",
						created_at: "2026-04-29T10:00:00Z",
						updated_at: "2026-04-29T10:00:00Z",
					},
				}),
			);
		});

		it("ハッピーパス: メモ入力後に追加ボタンを押すと PUT API が呼ばれ onSaved と onClose が実行される", async () => {
			// Arrange
			const user = userEvent.setup();
			const onClose = vi.fn();
			const onSaved = vi.fn();
			const onDeleted = vi.fn();

			// Act
			render(
				<RaceMarkMemoModalContainer
					open={true}
					mode="create"
					raceUid="abc001"
					columnId={101}
					raceEntryId={5}
					horseName="ディープスター"
					columnLabel="スポーツ報知"
					markValue="◎"
					onClose={onClose}
					onSaved={onSaved}
					onDeleted={onDeleted}
				/>,
			);
			await user.type(screen.getByLabelText("メモ"), "新規メモ");
			await user.click(screen.getByRole("button", { name: "追加" }));

			// Assert
			await waitFor(() => {
				expect(global.fetch).toHaveBeenCalledTimes(1);
			});
			const [url, init] = (global.fetch as ReturnType<typeof vi.fn>).mock
				.calls[0] as [string, RequestInit];
			expect(url).toBe("/api/races/abc001/mark-columns/101/entries/5/memo");
			expect(init.method).toBe("PUT");
			expect(JSON.parse(init.body as string)).toEqual({ content: "新規メモ" });
			expect(onSaved).toHaveBeenCalledWith({
				columnId: 101,
				raceEntryId: 5,
				content: "新規メモ",
			});
			expect(onClose).toHaveBeenCalledTimes(1);
		});
	});

	describe("edit モード", () => {
		it("削除ボタンを押すと DELETE API が呼ばれ onDeleted と onClose が実行される", async () => {
			// Arrange
			vi.spyOn(global, "fetch").mockResolvedValue(buildResponse(204, null));
			const user = userEvent.setup();
			const onClose = vi.fn();
			const onSaved = vi.fn();
			const onDeleted = vi.fn();

			// Act
			render(
				<RaceMarkMemoModalContainer
					open={true}
					mode="edit"
					raceUid="abc001"
					columnId={101}
					raceEntryId={5}
					horseName="ディープスター"
					columnLabel="スポーツ報知"
					markValue="○"
					initialContent="既存メモ"
					onClose={onClose}
					onSaved={onSaved}
					onDeleted={onDeleted}
				/>,
			);
			await user.click(screen.getByRole("button", { name: "削除" }));

			// Assert
			await waitFor(() => {
				expect(global.fetch).toHaveBeenCalledTimes(1);
			});
			const [url, init] = (global.fetch as ReturnType<typeof vi.fn>).mock
				.calls[0] as [string, RequestInit];
			expect(url).toBe("/api/races/abc001/mark-columns/101/entries/5/memo");
			expect(init.method).toBe("DELETE");
			expect(onDeleted).toHaveBeenCalledWith({ columnId: 101, raceEntryId: 5 });
			expect(onClose).toHaveBeenCalledTimes(1);
		});
	});
});
