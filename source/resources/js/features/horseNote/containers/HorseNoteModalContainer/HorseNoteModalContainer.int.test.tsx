import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import HorseNoteModalContainer from "./index";

describe("HorseNoteModalContainer", () => {
	beforeEach(() => {
		vi.spyOn(global, "fetch").mockResolvedValue(
			new Response(
				JSON.stringify({
					data: {
						id: 1,
						horse_id: 100,
						race_id: 200,
						race: {
							uid: "abc123",
							race_date: "2026-04-19",
							venue_name: "東京",
							race_number: 11,
							race_name: "皐月賞",
						},
						content: "新規メモ",
						created_at: "2026-04-29T10:00:00Z",
						updated_at: "2026-04-29T10:00:00Z",
					},
				}),
				{ status: 201, headers: { "Content-Type": "application/json" } },
			),
		);
	});

	afterEach(() => {
		vi.restoreAllMocks();
	});

	it("ハッピーパス: create モードでメモ入力後に追加ボタンを押すと POST API が呼ばれ onSuccess と onClose が実行される", async () => {
		// Arrange
		const user = userEvent.setup();
		const onClose = vi.fn();
		const onSuccess = vi.fn();
		render(
			<HorseNoteModalContainer
				open={true}
				mode="create"
				horseId={100}
				horseName="ディープスター"
				raceId={200}
				raceContext={{
					type: "fixed",
					label: "2026/04/19 東京 11R 皐月賞",
				}}
				onClose={onClose}
				onSuccess={onSuccess}
			/>,
		);

		// Act
		await user.type(screen.getByLabelText("メモ"), "新規メモ");
		await user.click(screen.getByRole("button", { name: "追加" }));

		// Assert
		await waitFor(() => {
			expect(global.fetch).toHaveBeenCalledTimes(1);
		});
		const [url, init] = (global.fetch as ReturnType<typeof vi.fn>).mock
			.calls[0] as [string, RequestInit];
		expect(url).toBe("/api/horses/100/notes");
		expect(init.method).toBe("POST");
		expect(JSON.parse(init.body as string)).toEqual({
			race_id: 200,
			content: "新規メモ",
		});
		expect(onSuccess).toHaveBeenCalledTimes(1);
		expect(onClose).toHaveBeenCalledTimes(1);
	});

	it("selectable raceContext で選択した uid に対応する race_id が POST body に渡る", async () => {
		// Arrange
		const user = userEvent.setup();
		const onClose = vi.fn();
		const onSuccess = vi.fn();
		render(
			<HorseNoteModalContainer
				open={true}
				mode="create"
				horseId={100}
				horseName="ディープスター"
				raceId={null}
				raceContext={{
					type: "selectable",
					options: [
						{ id: 11, uid: "abc001", label: "2026/04/19 東京 11R 皐月賞" },
						{ id: 22, uid: "abc002", label: "2026/04/26 中山 5R" },
					],
					defaultUid: null,
				}}
				onClose={onClose}
				onSuccess={onSuccess}
			/>,
		);

		// Act
		await user.selectOptions(
			screen.getByLabelText("紐づくレース（任意）"),
			"abc002",
		);
		await user.type(screen.getByLabelText("メモ"), "選択メモ");
		await user.click(screen.getByRole("button", { name: "追加" }));

		// Assert
		await waitFor(() => {
			expect(global.fetch).toHaveBeenCalledTimes(1);
		});
		const [, init] = (global.fetch as ReturnType<typeof vi.fn>).mock
			.calls[0] as [string, RequestInit];
		expect(JSON.parse(init.body as string)).toEqual({
			race_id: 22,
			content: "選択メモ",
		});
	});
});
