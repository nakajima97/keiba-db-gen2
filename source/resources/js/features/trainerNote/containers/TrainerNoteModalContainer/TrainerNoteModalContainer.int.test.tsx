import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { afterEach, describe, expect, it, vi } from "vitest";
import TrainerNoteModalContainer from "./index";

const buildResponse = (status: number, body: unknown) =>
	new Response(body == null ? null : JSON.stringify(body), {
		status,
		headers: { "Content-Type": "application/json" },
	});

describe("TrainerNoteModalContainer", () => {
	afterEach(() => {
		vi.restoreAllMocks();
	});

	describe("create モード", () => {
		it("ハッピーパス: メモ入力後に追加ボタンを押すと PUT API が呼ばれ onSaved と onClose が実行される", async () => {
			// Arrange
			vi.spyOn(global, "fetch").mockResolvedValue(
				buildResponse(201, {
					data: {
						id: 7,
						trainer_id: 12,
						content: "強気コメントは割り引く",
						created_at: "2026-11-22T10:00:00Z",
						updated_at: "2026-11-22T10:00:00Z",
					},
				}),
			);
			const user = userEvent.setup();
			const onClose = vi.fn();
			const onSaved = vi.fn();
			const onDeleted = vi.fn();

			// Act
			render(
				<TrainerNoteModalContainer
					open={true}
					mode="create"
					trainerId={12}
					trainerName="加藤 征弘"
					trainerAffiliation="美浦"
					onClose={onClose}
					onSaved={onSaved}
					onDeleted={onDeleted}
				/>,
			);
			await user.type(screen.getByLabelText("メモ"), "強気コメントは割り引く");
			await user.click(screen.getByRole("button", { name: "追加" }));

			// Assert
			await waitFor(() => {
				expect(global.fetch).toHaveBeenCalledTimes(1);
			});
			const [url, init] = (global.fetch as ReturnType<typeof vi.fn>).mock
				.calls[0] as [string, RequestInit];
			expect(url).toBe("/api/trainers/12/note");
			expect(init.method).toBe("PUT");
			expect(JSON.parse(init.body as string)).toEqual({
				content: "強気コメントは割り引く",
			});
			expect(onSaved).toHaveBeenCalledWith({
				trainerId: 12,
				id: 7,
				content: "強気コメントは割り引く",
			});
			expect(onClose).toHaveBeenCalledTimes(1);
		});

		it("API が 422 を返すとエラーメッセージが表示され onClose は呼ばれない", async () => {
			// Arrange
			vi.spyOn(global, "fetch").mockResolvedValue(
				buildResponse(422, {
					message: "invalid",
					errors: { content: ["メモは1000文字以内で入力してください。"] },
				}),
			);
			const user = userEvent.setup();
			const onClose = vi.fn();

			// Act
			render(
				<TrainerNoteModalContainer
					open={true}
					mode="create"
					trainerId={12}
					trainerName="加藤 征弘"
					trainerAffiliation="美浦"
					onClose={onClose}
					onSaved={vi.fn()}
					onDeleted={vi.fn()}
				/>,
			);
			await user.type(screen.getByLabelText("メモ"), "メモ");
			await user.click(screen.getByRole("button", { name: "追加" }));

			// Assert
			expect(
				await screen.findByText("メモは1000文字以内で入力してください。"),
			).toBeInTheDocument();
			expect(onClose).not.toHaveBeenCalled();
		});
	});

	describe("edit モード", () => {
		it("削除ボタンを押すと DELETE API が呼ばれ onDeleted と onClose が実行される", async () => {
			// Arrange
			vi.spyOn(global, "fetch").mockResolvedValue(buildResponse(204, null));
			const user = userEvent.setup();
			const onClose = vi.fn();
			const onDeleted = vi.fn();

			// Act
			render(
				<TrainerNoteModalContainer
					open={true}
					mode="edit"
					trainerId={12}
					trainerName="加藤 征弘"
					trainerAffiliation="美浦"
					initialContent="既存メモ"
					onClose={onClose}
					onSaved={vi.fn()}
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
			expect(url).toBe("/api/trainers/12/note");
			expect(init.method).toBe("DELETE");
			expect(onDeleted).toHaveBeenCalledWith({ trainerId: 12 });
			expect(onClose).toHaveBeenCalledTimes(1);
		});
	});
});
