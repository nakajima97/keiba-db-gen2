import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi } from "vitest";
import HorseNotesList from "./index";
import type { HorseNoteListItem } from "./types";

vi.mock("@inertiajs/react", () => ({
	Link: ({
		href,
		children,
	}: {
		href: string;
		children: React.ReactNode;
	}) => <a href={href}>{children}</a>,
}));

const raceLinkedNote: HorseNoteListItem = {
	id: 1,
	content: "前走は外枠で出遅れ気味。",
	race: { uid: "abc001", label: "2026/04/19 東京 11R 皐月賞" },
	created_at: "2026-04-19",
	updated_at: "2026-04-19",
};

const horseLinkedNote: HorseNoteListItem = {
	id: 2,
	content: "次走への備忘録メモ",
	race: null,
	created_at: "2026-04-20",
	updated_at: "2026-04-20",
};

describe("HorseNotesList", () => {
	describe("一覧の表示", () => {
		it("メモ一覧の本文が表示される", () => {
			// Act
			render(
				<HorseNotesList
					notes={[raceLinkedNote, horseLinkedNote]}
					onAddClick={() => {}}
					onEditClick={() => {}}
				/>,
			);

			// Assert
			expect(
				screen.getByText("前走は外枠で出遅れ気味。"),
			).toBeInTheDocument();
			expect(screen.getByText("次走への備忘録メモ")).toBeInTheDocument();
		});

		it("メモが 0 件の場合、空状態メッセージが表示される", () => {
			// Act
			render(
				<HorseNotesList
					notes={[]}
					onAddClick={() => {}}
					onEditClick={() => {}}
				/>,
			);

			// Assert
			expect(screen.getByText("メモがありません")).toBeInTheDocument();
		});
	});

	describe("メモ追加ボタン", () => {
		it("メモ追加ボタンが表示される", () => {
			// Act
			render(
				<HorseNotesList
					notes={[]}
					onAddClick={() => {}}
					onEditClick={() => {}}
				/>,
			);

			// Assert
			expect(
				screen.getByRole("button", { name: "メモを追加" }),
			).toBeInTheDocument();
		});

		it("メモ追加ボタンを押すと onAddClick が呼ばれる", async () => {
			// Arrange
			const onAddClick = vi.fn();
			const user = userEvent.setup();

			// Act
			render(
				<HorseNotesList
					notes={[]}
					onAddClick={onAddClick}
					onEditClick={() => {}}
				/>,
			);
			await user.click(screen.getByRole("button", { name: "メモを追加" }));

			// Assert
			expect(onAddClick).toHaveBeenCalledTimes(1);
		});
	});

	describe("編集操作", () => {
		it("編集ボタンを押すと onEditClick がメモ ID で呼ばれる", async () => {
			// Arrange
			const onEditClick = vi.fn();
			const user = userEvent.setup();

			// Act
			render(
				<HorseNotesList
					notes={[raceLinkedNote]}
					onAddClick={() => {}}
					onEditClick={onEditClick}
				/>,
			);
			await user.click(screen.getByRole("button", { name: "編集" }));

			// Assert
			expect(onEditClick).toHaveBeenCalledWith(1);
		});
	});

	describe("レース紐づきの差別化", () => {
		it("レース紐づきありメモには紐づきラベルとレースリンクが表示される", () => {
			// Act
			render(
				<HorseNotesList
					notes={[raceLinkedNote]}
					onAddClick={() => {}}
					onEditClick={() => {}}
				/>,
			);

			// Assert
			expect(screen.getByText("レース紐づき")).toBeInTheDocument();
			const link = screen.getByRole("link", {
				name: "2026/04/19 東京 11R 皐月賞",
			});
			expect(link).toHaveAttribute("href", "/races/abc001/result/edit");
		});

		it("レース紐づきなしメモにはレース紐づきなしラベルが表示される", () => {
			// Act
			render(
				<HorseNotesList
					notes={[horseLinkedNote]}
					onAddClick={() => {}}
					onEditClick={() => {}}
				/>,
			);

			// Assert
			expect(screen.getByText("レース紐づきなし")).toBeInTheDocument();
		});
	});
});
