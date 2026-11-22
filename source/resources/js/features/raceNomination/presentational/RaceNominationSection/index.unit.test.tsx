import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";
import type { RaceNomination } from "../../types/raceNomination";
import RaceNominationSection from "./index";

vi.mock("@inertiajs/react", () => ({
	Link: ({ href, children }: { href: string; children: React.ReactNode }) => (
		<a href={href}>{children}</a>
	),
}));

const nomination: RaceNomination = {
	id: 10,
	horse_id: 42,
	horse_uid: "horse-uid-42",
	horse_name: "ウワサノホース",
	status: "check",
	note: null,
};

const noopHandlers = {
	onStatusChange: vi.fn(),
	onNoteClick: vi.fn(),
	onDeleteClick: vi.fn(),
};

describe("RaceNominationSection", () => {
	it("登録馬がいないときは空のメッセージと追加フォームが表示される", () => {
		// Act
		render(
			<RaceNominationSection
				nominations={[]}
				addForm={<div>追加フォーム</div>}
				{...noopHandlers}
			/>,
		);

		// Assert
		expect(screen.getByText("登録馬はまだありません。")).toBeInTheDocument();
		expect(screen.getByText("追加フォーム")).toBeInTheDocument();
	});

	it("登録馬の馬名が競走馬詳細へのリンクで表示され、チェック／消しの選択状態が反映される", () => {
		// Act
		render(
			<RaceNominationSection
				nominations={[nomination]}
				addForm={null}
				{...noopHandlers}
			/>,
		);

		// Assert
		expect(screen.getByRole("link", { name: "ウワサノホース" })).toHaveAttribute(
			"href",
			"/horses/horse-uid-42",
		);
		expect(
			screen.getByRole("radio", { name: "ウワサノホースをチェック" }),
		).toHaveAttribute("aria-checked", "true");
	});

	it("メモ・削除・チェック／消しの操作で対応するハンドラが呼ばれる", async () => {
		// Arrange
		const user = userEvent.setup();
		const handlers = {
			onStatusChange: vi.fn(),
			onNoteClick: vi.fn(),
			onDeleteClick: vi.fn(),
		};
		render(
			<RaceNominationSection
				nominations={[nomination]}
				addForm={null}
				{...handlers}
			/>,
		);

		// Act
		await user.click(screen.getByRole("button", { name: "ウワサノホースのメモ" }));
		await user.click(
			screen.getByRole("button", { name: "ウワサノホースを登録馬から削除" }),
		);
		await user.click(screen.getByRole("radio", { name: "ウワサノホースを消し" }));

		// Assert
		expect(handlers.onNoteClick).toHaveBeenCalledWith(42);
		expect(handlers.onDeleteClick).toHaveBeenCalledWith(10);
		expect(handlers.onStatusChange).toHaveBeenCalledWith(10, "excluded");
	});
});
