import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi } from "vitest";
import HorseNoteDeleteConfirmDialog from "./index";
import type { HorseNoteDeleteConfirmDialogProps } from "./types";

const baseProps: HorseNoteDeleteConfirmDialogProps = {
	open: true,
	noteContentPreview: "削除対象メモの本文プレビュー",
	submitting: false,
	errorMessage: null,
	onOpenChange: () => {},
	onConfirm: () => {},
};

describe("HorseNoteDeleteConfirmDialog", () => {
	describe("表示制御", () => {
		it("open=true の場合、ダイアログが表示される", () => {
			// Act
			render(<HorseNoteDeleteConfirmDialog {...baseProps} open={true} />);

			// Assert
			expect(screen.getByRole("dialog")).toBeInTheDocument();
		});

		it("open=false の場合、ダイアログが表示されない", () => {
			// Act
			render(<HorseNoteDeleteConfirmDialog {...baseProps} open={false} />);

			// Assert
			expect(screen.queryByRole("dialog")).not.toBeInTheDocument();
		});
	});

	describe("コンテンツ表示", () => {
		it("noteContentPreview に指定したメモ本文プレビューが表示される", () => {
			// Act
			render(
				<HorseNoteDeleteConfirmDialog
					{...baseProps}
					noteContentPreview="プレビュー本文テキスト"
				/>,
			);

			// Assert
			expect(screen.getByText("プレビュー本文テキスト")).toBeInTheDocument();
		});
	});

	describe("操作", () => {
		it("「削除する」ボタンを押すと onConfirm が呼ばれる", async () => {
			// Arrange
			const onConfirm = vi.fn();
			const user = userEvent.setup();

			// Act
			render(
				<HorseNoteDeleteConfirmDialog
					{...baseProps}
					onConfirm={onConfirm}
				/>,
			);
			await user.click(screen.getByRole("button", { name: "削除する" }));

			// Assert
			expect(onConfirm).toHaveBeenCalledTimes(1);
		});

		it("「キャンセル」ボタンを押すと onOpenChange が false で呼ばれる", async () => {
			// Arrange
			const onOpenChange = vi.fn();
			const user = userEvent.setup();

			// Act
			render(
				<HorseNoteDeleteConfirmDialog
					{...baseProps}
					onOpenChange={onOpenChange}
				/>,
			);
			await user.click(screen.getByRole("button", { name: "キャンセル" }));

			// Assert
			expect(onOpenChange).toHaveBeenCalledWith(false);
		});
	});

	describe("送信ボタンの状態", () => {
		it("submitting=true の場合、「削除する」ボタンが無効化される", () => {
			// Act
			render(
				<HorseNoteDeleteConfirmDialog {...baseProps} submitting={true} />,
			);

			// Assert
			expect(
				screen.getByRole("button", { name: "削除する" }),
			).toBeDisabled();
		});
	});
});
