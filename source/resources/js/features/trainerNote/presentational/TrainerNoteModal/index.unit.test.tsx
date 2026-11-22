import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import TrainerNoteModal from "./index";
import type { TrainerNoteModalProps } from "./types";

const baseProps: TrainerNoteModalProps = {
	open: true,
	mode: "create",
	trainerName: "加藤 征弘",
	trainerAffiliation: "美浦",
	content: "",
	contentMaxLength: 1000,
	errorMessage: null,
	submitting: false,
	onContentChange: () => {},
	onOpenChange: () => {},
	onSubmit: () => {},
};

describe("TrainerNoteModal", () => {
	it('mode="create" の場合、タイトルが「調教師メモを追加」で削除ボタンは表示されない', () => {
		// Act
		render(<TrainerNoteModal {...baseProps} onDelete={() => {}} />);

		// Assert
		expect(screen.getByText("調教師メモを追加")).toBeInTheDocument();
		expect(
			screen.queryByRole("button", { name: "削除" }),
		).not.toBeInTheDocument();
	});

	it('mode="edit" の場合、タイトルが「調教師メモを編集」で削除ボタンが表示される', () => {
		// Act
		render(
			<TrainerNoteModal
				{...baseProps}
				mode="edit"
				content="既存メモ"
				onDelete={() => {}}
			/>,
		);

		// Assert
		expect(screen.getByText("調教師メモを編集")).toBeInTheDocument();
		expect(screen.getByRole("button", { name: "削除" })).toBeInTheDocument();
	});

	it("調教師名と所属が表示される", () => {
		// Act
		render(<TrainerNoteModal {...baseProps} />);

		// Assert
		expect(screen.getByText("加藤 征弘（美浦）")).toBeInTheDocument();
	});

	it("本文が空の場合、追加ボタンが無効になる", () => {
		// Act
		render(<TrainerNoteModal {...baseProps} content="   " />);

		// Assert
		expect(screen.getByRole("button", { name: "追加" })).toBeDisabled();
	});

	it("本文が文字数上限を超える場合、追加ボタンが無効になる", () => {
		// Act
		render(
			<TrainerNoteModal
				{...baseProps}
				content={"a".repeat(1001)}
				contentMaxLength={1000}
			/>,
		);

		// Assert
		expect(screen.getByRole("button", { name: "追加" })).toBeDisabled();
	});
});
