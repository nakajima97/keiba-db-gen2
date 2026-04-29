import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi } from "vitest";
import HorseNoteModal from "./index";
import type { HorseNoteModalProps } from "./types";

const baseProps: HorseNoteModalProps = {
	open: true,
	mode: "create",
	horseName: "ディープスター",
	content: "",
	contentMaxLength: 1000,
	errorMessage: null,
	submitting: false,
	raceContext: { type: "fixed", label: "2026/04/19 東京 11R 皐月賞" },
	onContentChange: () => {},
	onRaceSelect: () => {},
	onOpenChange: () => {},
	onSubmit: () => {},
};

describe("HorseNoteModal", () => {
	describe("表示制御", () => {
		it("open=true の場合、モーダルが表示される", () => {
			// Act
			render(<HorseNoteModal {...baseProps} open={true} />);

			// Assert
			expect(screen.getByRole("dialog")).toBeInTheDocument();
		});

		it("open=false の場合、モーダルが表示されない", () => {
			// Act
			render(<HorseNoteModal {...baseProps} open={false} />);

			// Assert
			expect(screen.queryByRole("dialog")).not.toBeInTheDocument();
		});
	});

	describe("モードによる表示の差別化", () => {
		it('mode="create" の場合、タイトルが「メモを追加」になる', () => {
			// Act
			render(<HorseNoteModal {...baseProps} mode="create" />);

			// Assert
			expect(screen.getByText("メモを追加")).toBeInTheDocument();
		});

		it('mode="edit" の場合、タイトルが「メモを編集」になる', () => {
			// Act
			render(<HorseNoteModal {...baseProps} mode="edit" />);

			// Assert
			expect(screen.getByText("メモを編集")).toBeInTheDocument();
		});

		it('mode="create" の場合、送信ボタン文言が「追加」になる', () => {
			// Act
			render(
				<HorseNoteModal {...baseProps} mode="create" content="テスト" />,
			);

			// Assert
			expect(
				screen.getByRole("button", { name: "追加" }),
			).toBeInTheDocument();
		});

		it('mode="edit" の場合、送信ボタン文言が「保存」になる', () => {
			// Act
			render(<HorseNoteModal {...baseProps} mode="edit" content="テスト" />);

			// Assert
			expect(
				screen.getByRole("button", { name: "保存" }),
			).toBeInTheDocument();
		});
	});

	describe("コンテンツ入力", () => {
		it("content が textarea に表示される", () => {
			// Act
			render(<HorseNoteModal {...baseProps} content="既存メモ" />);

			// Assert
			expect(screen.getByDisplayValue("既存メモ")).toBeInTheDocument();
		});

		it("textarea に入力すると onContentChange が入力値で呼ばれる", async () => {
			// Arrange
			const onContentChange = vi.fn();
			const user = userEvent.setup();

			// Act
			render(
				<HorseNoteModal
					{...baseProps}
					content=""
					onContentChange={onContentChange}
				/>,
			);
			await user.type(screen.getByLabelText("メモ"), "あ");

			// Assert
			expect(onContentChange).toHaveBeenCalledWith("あ");
		});

		it("文字数カウンタが表示される", () => {
			// Act
			render(
				<HorseNoteModal
					{...baseProps}
					content="あいうえお"
					contentMaxLength={1000}
				/>,
			);

			// Assert
			expect(screen.getByText("5 / 1000")).toBeInTheDocument();
		});
	});

	describe("レースコンテキスト", () => {
		it('raceContext.type="fixed" の場合、ラベルが表示される', () => {
			// Act
			render(
				<HorseNoteModal
					{...baseProps}
					raceContext={{
						type: "fixed",
						label: "2026/04/19 東京 11R 皐月賞",
					}}
				/>,
			);

			// Assert
			expect(
				screen.getByText("2026/04/19 東京 11R 皐月賞"),
			).toBeInTheDocument();
		});

		it('raceContext.type="selectable" の場合、レース選択肢が表示される', () => {
			// Act
			render(
				<HorseNoteModal
					{...baseProps}
					raceContext={{
						type: "selectable",
						options: [
							{ uid: "abc001", label: "2026/04/19 東京 11R 皐月賞" },
							{ uid: "abc002", label: "2026/04/26 東京 9R 1勝クラス" },
						],
						selectedUid: null,
					}}
				/>,
			);

			// Assert
			expect(
				screen.getByRole("option", { name: "2026/04/19 東京 11R 皐月賞" }),
			).toBeInTheDocument();
			expect(
				screen.getByRole("option", { name: "2026/04/26 東京 9R 1勝クラス" }),
			).toBeInTheDocument();
		});

		it('raceContext.type="selectable" でレース選択時に onRaceSelect が UID で呼ばれる', async () => {
			// Arrange
			const onRaceSelect = vi.fn();
			const user = userEvent.setup();

			// Act
			render(
				<HorseNoteModal
					{...baseProps}
					raceContext={{
						type: "selectable",
						options: [
							{ uid: "abc001", label: "2026/04/19 東京 11R 皐月賞" },
						],
						selectedUid: null,
					}}
					onRaceSelect={onRaceSelect}
				/>,
			);
			await user.selectOptions(
				screen.getByLabelText("紐づくレース（任意）"),
				"abc001",
			);

			// Assert
			expect(onRaceSelect).toHaveBeenCalledWith("abc001");
		});

		it('raceContext.type="none" の場合、レース選択 UI が表示されない', () => {
			// Act
			render(
				<HorseNoteModal {...baseProps} raceContext={{ type: "none" }} />,
			);

			// Assert
			expect(
				screen.queryByLabelText("紐づくレース（任意）"),
			).not.toBeInTheDocument();
			expect(screen.queryByText("紐づくレース")).not.toBeInTheDocument();
		});
	});

	describe("送信ボタンの状態", () => {
		it("submitting=true の場合、送信ボタンが無効化される", () => {
			// Act
			render(
				<HorseNoteModal
					{...baseProps}
					content="メモ"
					submitting={true}
				/>,
			);

			// Assert
			expect(screen.getByRole("button", { name: "追加" })).toBeDisabled();
		});
	});

	describe("エラー表示", () => {
		it("errorMessage が表示される", () => {
			// Act
			render(
				<HorseNoteModal
					{...baseProps}
					errorMessage="同じレースに対するメモは既に存在します"
				/>,
			);

			// Assert
			expect(
				screen.getByText("同じレースに対するメモは既に存在します"),
			).toBeInTheDocument();
		});
	});

	describe("操作", () => {
		it("送信ボタンを押すと onSubmit が呼ばれる", async () => {
			// Arrange
			const onSubmit = vi.fn();
			const user = userEvent.setup();

			// Act
			render(
				<HorseNoteModal
					{...baseProps}
					content="テストメモ"
					onSubmit={onSubmit}
				/>,
			);
			await user.click(screen.getByRole("button", { name: "追加" }));

			// Assert
			expect(onSubmit).toHaveBeenCalledTimes(1);
		});

		it("キャンセルボタンを押すと onOpenChange が false で呼ばれる", async () => {
			// Arrange
			const onOpenChange = vi.fn();
			const user = userEvent.setup();

			// Act
			render(
				<HorseNoteModal {...baseProps} onOpenChange={onOpenChange} />,
			);
			await user.click(screen.getByRole("button", { name: "キャンセル" }));

			// Assert
			expect(onOpenChange).toHaveBeenCalledWith(false);
		});
	});
});
