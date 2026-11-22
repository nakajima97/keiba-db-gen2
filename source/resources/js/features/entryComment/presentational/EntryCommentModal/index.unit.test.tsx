import { render, screen, within } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";
import type { EntryComment } from "../../types";
import EntryCommentModal from "./index";
import type { EntryCommentModalProps } from "./types";

const comment: EntryComment = {
	id: 2,
	speaker_type: "jockey",
	speaker_id: 20,
	speaker_name: "ルメール",
	sentiment: "negative",
	content: "今回は様子見",
	outcome: null,
	created_at: "2026-11-22T10:00:00+09:00",
};

const buildProps = (overrides: Partial<EntryCommentModalProps> = {}): EntryCommentModalProps => ({
	open: true,
	horseName: "ディープスター",
	trainerName: "加藤 征弘",
	jockeyName: "ルメール",
	comments: [comment],
	editingKey: null,
	form: { speakerType: "trainer", sentiment: "positive", content: "", outcome: null },
	contentMaxLength: 1000,
	errorMessage: null,
	submitting: false,
	pendingKeys: [],
	onOpenChange: () => {},
	onFormChange: () => {},
	onSubmit: () => {},
	onEditClick: () => {},
	onCancelEdit: () => {},
	onDeleteClick: () => {},
	onOutcomeChange: () => {},
	...overrides,
});

describe("EntryCommentModal", () => {
	it("記録済みコメントの発言者・向き・本文を表示する", () => {
		// Act
		render(<EntryCommentModal {...buildProps()} />);

		// Assert
		const list = within(screen.getByRole("list"));
		expect(list.getByText("今回は様子見")).toBeInTheDocument();
		expect(list.getByText("弱気")).toBeInTheDocument();
		expect(list.getByText("騎手")).toBeInTheDocument();
		expect(list.getByText("ルメール")).toBeInTheDocument();
	});

	it("コメントがないときは「コメントはまだありません」と表示する", () => {
		// Act
		render(<EntryCommentModal {...buildProps({ comments: [] })} />);

		// Assert
		expect(screen.getByText("コメントはまだありません")).toBeInTheDocument();
	});

	it("調教師が未登録のときは発言者の「調教師」を選べない", () => {
		// Act
		render(
			<EntryCommentModal
				{...buildProps({
					trainerName: null,
					form: { speakerType: "jockey", sentiment: "positive", content: "", outcome: null },
				})}
			/>,
		);

		// Assert
		expect(screen.getByRole("radio", { name: "調教師（未登録）" })).toBeDisabled();
	});

	it("コメント内容が空なら追加ボタンは押せない", () => {
		// Act
		render(<EntryCommentModal {...buildProps()} />);

		// Assert
		expect(screen.getByRole("button", { name: "追加" })).toBeDisabled();
	});

	it("コメント内容が上限を超えると追加ボタンは押せない", () => {
		// Act
		render(
			<EntryCommentModal
				{...buildProps({
					contentMaxLength: 3,
					form: { speakerType: "trainer", sentiment: "positive", content: "abcd", outcome: null },
				})}
			/>,
		);

		// Assert
		expect(screen.getByRole("button", { name: "追加" })).toBeDisabled();
	});

	it("編集中は見出しが「コメントを編集」になり保存ボタンと編集をやめるボタンを表示する", () => {
		// Act
		render(
			<EntryCommentModal
				{...buildProps({
					editingKey: "jockey-2",
					form: { speakerType: "jockey", sentiment: "negative", content: "今回は様子見", outcome: null },
				})}
			/>,
		);

		// Assert
		expect(screen.getByRole("heading", { name: "コメントを編集" })).toBeInTheDocument();
		expect(screen.getByRole("button", { name: "保存" })).toBeEnabled();
		expect(screen.getByRole("button", { name: "編集をやめる" })).toBeInTheDocument();
	});

	it("編集・削除ボタンを押すと対象のコメントを渡してハンドラが呼ばれる", async () => {
		// Arrange
		const onEditClick = vi.fn();
		const onDeleteClick = vi.fn();
		const user = userEvent.setup();
		render(<EntryCommentModal {...buildProps({ onEditClick, onDeleteClick })} />);

		// Act
		await user.click(screen.getByRole("button", { name: "コメントを編集" }));
		await user.click(screen.getByRole("button", { name: "コメントを削除" }));

		// Assert
		expect(onEditClick).toHaveBeenCalledWith(comment);
		expect(onDeleteClick).toHaveBeenCalledWith(comment);
	});

	it("エラーメッセージがあれば表示する", () => {
		// Act
		render(<EntryCommentModal {...buildProps({ errorMessage: "保存に失敗しました" })} />);

		// Assert
		expect(screen.getByText("保存に失敗しました")).toBeInTheDocument();
	});
});
