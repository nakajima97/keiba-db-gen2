import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi } from "vitest";
import RaceDetail from "./index";
import type { RaceDetailItem } from "./types";

vi.mock("@inertiajs/react", () => ({
	Link: ({
		href,
		children,
	}: {
		href: string;
		children: React.ReactNode;
	}) => <a href={href}>{children}</a>,
}));

const baseRace: RaceDetailItem = {
	uid: "abc123",
	race_date: "2026-04-05",
	venue_name: "東京",
	race_number: 3,
	race_name: "皐月賞",
	entries: [
		{
			id: 1,
			uid: "entry-uid-1",
			horse_id: 42,
			horse_uid: "horse-uid-42",
			frame_number: 2,
			horse_number: 1,
			horse_name: "テストホース",
			jockey_name: "テスト騎手",
			trainer_id: 10,
			trainer_name: "テスト調教師",
			trainer_affiliation: "美浦",
			weight: 480,
		},
	],
	mark_columns: [{ id: 100, type: "own", label: null, display_order: 0 }],
	marks: [],
	has_result: false,
};

const noopHandlers = {
	onMarkChange: vi.fn(),
	onAddOtherColumn: vi.fn(),
	onRemoveOtherColumn: vi.fn(),
	onChangeColumnLabel: vi.fn(),
};

describe("RaceDetail", () => {
	describe("基本情報", () => {
		it("開催日が表示される", () => {
			// Act
			render(<RaceDetail race={baseRace} {...noopHandlers} />);

			// Assert
			expect(screen.getByText("2026/04/05")).toBeInTheDocument();
		});

		it("競馬場が表示される", () => {
			// Act
			render(<RaceDetail race={baseRace} {...noopHandlers} />);

			// Assert
			expect(screen.getByText("東京")).toBeInTheDocument();
		});

		it("レース番号が「3R」形式で表示される", () => {
			// Act
			render(<RaceDetail race={baseRace} {...noopHandlers} />);

			// Assert
			expect(screen.getByText("3R")).toBeInTheDocument();
		});

		it("レース名が表示される", () => {
			// Act
			render(<RaceDetail race={baseRace} {...noopHandlers} />);

			// Assert
			expect(screen.getByText("レース名")).toBeInTheDocument();
			expect(screen.getByText("皐月賞")).toBeInTheDocument();
		});

		it("レース名が null の場合「—」が表示される", () => {
			// Arrange
			const raceWithNullName: RaceDetailItem = {
				...baseRace,
				race_name: null,
			};

			// Act
			render(<RaceDetail race={raceWithNullName} {...noopHandlers} />);

			// Assert
			expect(screen.getByText("レース名")).toBeInTheDocument();
			expect(screen.getByText("—")).toBeInTheDocument();
		});
	});

	describe("出馬表", () => {
		it("枠番・馬番・馬名・騎手名・馬体重が表示される", () => {
			// Act
			render(<RaceDetail race={baseRace} {...noopHandlers} />);

			// Assert
			expect(screen.getByText("テストホース")).toBeInTheDocument();
			expect(screen.getByText("テスト騎手")).toBeInTheDocument();
			expect(screen.getByText("480kg")).toBeInTheDocument();
		});

		it("調教師名と所属が表示される", () => {
			// Act
			render(<RaceDetail race={baseRace} {...noopHandlers} />);

			// Assert
			expect(screen.getByText("調教師")).toBeInTheDocument();
			expect(screen.getByText("テスト調教師")).toBeInTheDocument();
			expect(screen.getByText("(美浦)")).toBeInTheDocument();
		});

		it("調教師メモのアイコンをクリックすると onTrainerNoteClick が trainer_id で呼ばれる", async () => {
			// Arrange
			const user = userEvent.setup();
			const onTrainerNoteClick = vi.fn();
			render(
				<RaceDetail
					race={baseRace}
					{...noopHandlers}
					onTrainerNoteClick={onTrainerNoteClick}
				/>,
			);

			// Act
			await user.click(
				screen.getByRole("button", { name: "テスト調教師の調教師メモ" }),
			);

			// Assert
			expect(onTrainerNoteClick).toHaveBeenCalledWith(10);
		});

		it("調教師が未登録（null）の場合「-」が表示される", () => {
			// Arrange
			const raceWithoutTrainer: RaceDetailItem = {
				...baseRace,
				entries: [
					{
						...baseRace.entries[0],
						trainer_id: null,
						trainer_name: null,
						trainer_affiliation: null,
					},
				],
			};

			// Act
			render(<RaceDetail race={raceWithoutTrainer} {...noopHandlers} />);

			// Assert
			expect(screen.getByText("-")).toBeInTheDocument();
		});

		it("馬体重が null の場合「-」が表示される", () => {
			// Arrange
			const raceWithNullWeight: RaceDetailItem = {
				...baseRace,
				entries: [{ ...baseRace.entries[0], weight: null }],
			};

			// Act
			render(<RaceDetail race={raceWithNullWeight} {...noopHandlers} />);

			// Assert
			expect(screen.getByText("-")).toBeInTheDocument();
		});

		it("馬名が競走馬詳細ページへのリンクとして表示される", () => {
			// Act
			render(<RaceDetail race={baseRace} {...noopHandlers} />);

			// Assert
			const link = screen.getByRole("link", { name: "テストホース" });
			expect(link).toHaveAttribute("href", "/horses/horse-uid-42");
		});
	});

	describe("印の列", () => {
		it("自分の印列のヘッダーが表示される", () => {
			// Act
			render(<RaceDetail race={baseRace} {...noopHandlers} />);

			// Assert
			expect(screen.getByText("自分")).toBeInTheDocument();
		});

		it("他人の印を追加ボタンが表示される", () => {
			// Act
			render(<RaceDetail race={baseRace} {...noopHandlers} />);

			// Assert
			expect(
				screen.getByRole("button", { name: "他人の印を追加" }),
			).toBeInTheDocument();
		});

		it("other 列がある場合にそのラベルがヘッダーに表示される", () => {
			// Arrange
			const raceWithOtherColumn: RaceDetailItem = {
				...baseRace,
				mark_columns: [
					{ id: 100, type: "own", label: null, display_order: 0 },
					{ id: 101, type: "other", label: "友人A", display_order: 1 },
				],
			};

			// Act
			render(<RaceDetail race={raceWithOtherColumn} {...noopHandlers} />);

			// Assert
			expect(screen.getByDisplayValue("友人A")).toBeInTheDocument();
		});

		it("出走馬の行に印セレクト（role=combobox）が表示される", () => {
			// Act
			render(<RaceDetail race={baseRace} {...noopHandlers} />);

			// Assert
			expect(screen.getByRole("combobox")).toBeInTheDocument();
		});

		it("mark が設定されている出走馬の印セレクトに設定値が反映される", () => {
			// Arrange
			const raceWithMark: RaceDetailItem = {
				...baseRace,
				marks: [{ column_id: 100, race_entry_id: 1, mark_value: "◎" }],
			};

			// Act
			render(<RaceDetail race={raceWithMark} {...noopHandlers} />);

			// Assert
			expect(screen.getByRole("combobox")).toHaveTextContent("◎");
		});

		it("「他人の印を追加」ボタンを押すと onAddOtherColumn が呼ばれる", async () => {
			// Arrange
			const onAddOtherColumn = vi.fn();
			const user = userEvent.setup();

			// Act
			render(
				<RaceDetail
					race={baseRace}
					{...noopHandlers}
					onAddOtherColumn={onAddOtherColumn}
				/>,
			);
			await user.click(
				screen.getByRole("button", { name: "他人の印を追加" }),
			);

			// Assert
			expect(onAddOtherColumn).toHaveBeenCalledTimes(1);
		});

		it("他人の印列の削除ボタンを押すと onRemoveOtherColumn が列 ID で呼ばれる", async () => {
			// Arrange
			const onRemoveOtherColumn = vi.fn();
			const user = userEvent.setup();
			const raceWithOtherColumn: RaceDetailItem = {
				...baseRace,
				mark_columns: [
					{ id: 100, type: "own", label: null, display_order: 0 },
					{ id: 101, type: "other", label: "友人A", display_order: 1 },
				],
			};

			// Act
			render(
				<RaceDetail
					race={raceWithOtherColumn}
					{...noopHandlers}
					onRemoveOtherColumn={onRemoveOtherColumn}
				/>,
			);
			await user.click(screen.getByRole("button", { name: "この印列を削除" }));

			// Assert
			expect(onRemoveOtherColumn).toHaveBeenCalledWith(101);
		});

		it("他人の印列のラベルを変更すると onChangeColumnLabel が列 ID と入力値で呼ばれる", async () => {
			// Arrange
			const onChangeColumnLabel = vi.fn();
			const user = userEvent.setup();
			const raceWithOtherColumn: RaceDetailItem = {
				...baseRace,
				mark_columns: [
					{ id: 100, type: "own", label: null, display_order: 0 },
					{ id: 101, type: "other", label: "", display_order: 1 },
				],
			};

			// Act
			render(
				<RaceDetail
					race={raceWithOtherColumn}
					{...noopHandlers}
					onChangeColumnLabel={onChangeColumnLabel}
				/>,
			);
			await user.type(screen.getByLabelText("他人の印列のラベル"), "X");

			// Assert
			expect(onChangeColumnLabel).toHaveBeenCalledWith(101, "X");
		});

		it("印を選択すると onMarkChange が columnId/raceEntryId/markValue で呼ばれる", async () => {
			// Arrange
			const onMarkChange = vi.fn();
			const user = userEvent.setup();

			// Act
			render(
				<RaceDetail
					race={baseRace}
					{...noopHandlers}
					onMarkChange={onMarkChange}
				/>,
			);
			await user.click(screen.getByRole("combobox"));
			await user.click(screen.getByRole("option", { name: "◎" }));

			// Assert
			expect(onMarkChange).toHaveBeenCalledWith({
				columnId: 100,
				raceEntryId: 1,
				markValue: "◎",
			});
		});
	});
});

describe("登録馬のチェック／消しの引き継ぎ", () => {
	it("nominationStatusByHorseId に含まれる馬は馬名の横にバッジが表示される", () => {
		// Act
		render(
			<RaceDetail
				race={baseRace}
				nominationStatusByHorseId={{ 42: "excluded" }}
				{...noopHandlers}
			/>,
		);

		// Assert
		expect(screen.getByLabelText("登録馬で消し")).toHaveTextContent("消");
	});

	it("nominationStatusByHorseId に含まれない馬にはバッジが表示されない", () => {
		// Act
		render(<RaceDetail race={baseRace} {...noopHandlers} />);

		// Assert
		expect(screen.queryByLabelText("登録馬で消し")).not.toBeInTheDocument();
		expect(screen.queryByLabelText("登録馬でチェック済み")).not.toBeInTheDocument();
	});

	describe("調教師・騎手コメント", () => {
		const stats = {
			positive: { hit: 3, judged: 10 },
			conditional: { hit: 5, judged: 8 },
			negative: { hit: 0, judged: 0 },
		};
		const raceWithComments: RaceDetailItem = {
			...baseRace,
			entries: [
				{
					...baseRace.entries[0],
					jockey_id: 20,
					comments: [
						{
							id: 1,
							speaker_type: "jockey",
							speaker_id: 20,
							speaker_name: "テスト騎手",
							sentiment: "positive",
							content: "好感触",
							outcome: null,
							created_at: "2026-04-01T10:00:00+09:00",
						},
					],
					trainer_comment_stats: {
						positive: { hit: 1, judged: 2 },
						conditional: { hit: 0, judged: 0 },
						negative: { hit: 0, judged: 0 },
					},
					jockey_comment_stats: stats,
				},
			],
		};

		it("騎手名・調教師名の横に向き別の的中率が表示される", () => {
			// Act
			render(<RaceDetail race={raceWithComments} {...noopHandlers} />);

			// Assert
			expect(screen.getByText("前 3/10・条 5/8")).toBeInTheDocument();
			expect(screen.getByText("前 1/2")).toBeInTheDocument();
		});

		it("コメント件数がバッジで表示され、アイコンのクリックで出走馬IDを渡して onCommentClick が呼ばれる", async () => {
			// Arrange
			const onCommentClick = vi.fn();
			const user = userEvent.setup();
			render(
				<RaceDetail
					race={raceWithComments}
					{...noopHandlers}
					onCommentClick={onCommentClick}
				/>,
			);

			// Act
			await user.click(
				screen.getByRole("button", {
					name: "テストホースの調教師・騎手コメント",
				}),
			);

			// Assert
			expect(screen.getByLabelText("1件")).toHaveTextContent("1");
			expect(onCommentClick).toHaveBeenCalledWith(1);
		});
	});
});
