import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";
import type { RaceMarkColumn } from "@/features/raceDetail/presentational/RaceDetail/types";
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
	markColumns: [],
	marks: [],
	markMemos: [],
	onStatusChange: vi.fn(),
	onNoteClick: vi.fn(),
	onDeleteClick: vi.fn(),
	onAddOtherColumn: vi.fn(),
	onChangeColumnLabel: vi.fn(),
	onRemoveOtherColumn: vi.fn(),
	onMarkChange: vi.fn(),
	onMarkMemoClick: vi.fn(),
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
			...noopHandlers,
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

	describe("他人の印", () => {
		const markColumns: RaceMarkColumn[] = [
			{ id: 3, type: "other", label: "新聞B", display_order: 2 },
			{ id: 1, type: "own", label: null, display_order: 0 },
			{ id: 2, type: "other", label: "Aさん", display_order: 1 },
		];

		it("他人の印列だけが display_order 順に表示され自分の印列は表示されない", () => {
			// Act
			render(
				<RaceNominationSection
					{...noopHandlers}
					nominations={[nomination]}
					addForm={null}
					markColumns={markColumns}
				/>,
			);

			// Assert
			const labels = screen
				.getAllByLabelText("他人の印列のラベル")
				.map((input) => (input as HTMLInputElement).value);
			expect(labels).toEqual(["Aさん", "新聞B"]);
			expect(screen.queryByText("自分")).not.toBeInTheDocument();
			expect(
				screen.getByRole("combobox", { name: "ウワサノホースの印（Aさん）" }),
			).toBeInTheDocument();
		});

		it("印メモアイコンは印か印メモがあるセルにだけ表示され、押すと対象セルで onMarkMemoClick が呼ばれる", async () => {
			// Arrange
			const user = userEvent.setup();
			const onMarkMemoClick = vi.fn();
			render(
				<RaceNominationSection
					{...noopHandlers}
					nominations={[nomination]}
					addForm={null}
					markColumns={[
						...markColumns,
						{ id: 4, type: "other", label: "Cさん", display_order: 3 },
					]}
					marks={[{ column_id: 2, race_nomination_id: 10, mark_value: "◎" }]}
					markMemos={[
						{ column_id: 3, race_nomination_id: 10, content: "注目" },
					]}
					onMarkMemoClick={onMarkMemoClick}
				/>,
			);

			// Act
			await user.click(
				screen.getByRole("button", { name: "ウワサノホースの新聞Bの印メモ" }),
			);

			// Assert
			expect(
				screen.getByRole("combobox", { name: "ウワサノホースの印（Aさん）" }),
			).toHaveTextContent("◎");
			expect(
				screen.getByRole("button", { name: "ウワサノホースのAさんの印メモ" }),
			).toBeInTheDocument();
			expect(
				screen.queryByRole("button", { name: "ウワサノホースのCさんの印メモ" }),
			).not.toBeInTheDocument();
			expect(onMarkMemoClick).toHaveBeenCalledWith({
				columnId: 3,
				raceNominationId: 10,
			});
		});

		it("印の選択・列の追加・ラベル変更・列の削除で対応するハンドラが呼ばれる", async () => {
			// Arrange
			const user = userEvent.setup();
			const handlers = {
				...noopHandlers,
				onMarkChange: vi.fn(),
				onAddOtherColumn: vi.fn(),
				onChangeColumnLabel: vi.fn(),
				onRemoveOtherColumn: vi.fn(),
			};
			render(
				<RaceNominationSection
					{...handlers}
					nominations={[nomination]}
					addForm={null}
					markColumns={[markColumns[2]]}
				/>,
			);

			// Act
			await user.click(
				screen.getByRole("combobox", { name: "ウワサノホースの印（Aさん）" }),
			);
			await user.click(screen.getByRole("option", { name: "▲" }));
			await user.click(screen.getByRole("button", { name: "他人の印を追加" }));
			await user.type(screen.getByLabelText("他人の印列のラベル"), "X");
			await user.click(screen.getByRole("button", { name: "この印列を削除" }));

			// Assert
			expect(handlers.onMarkChange).toHaveBeenCalledWith({
				columnId: 2,
				raceNominationId: 10,
				markValue: "▲",
			});
			expect(handlers.onAddOtherColumn).toHaveBeenCalledTimes(1);
			expect(handlers.onChangeColumnLabel).toHaveBeenCalledWith(2, "AさんX");
			expect(handlers.onRemoveOtherColumn).toHaveBeenCalledWith(2);
		});
	});
});
