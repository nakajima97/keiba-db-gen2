import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi, beforeEach } from "vitest";
import RaceInputForm from "./index";

vi.mock("@/components/shadcn/ui/select", () => ({
	Select: ({
		value,
		onValueChange,
		children,
	}: {
		value: string;
		onValueChange: (v: string) => void;
		children: React.ReactNode;
	}) => (
		<div data-value={value}>
			<button
				onClick={() => onValueChange("1")}
				type="button"
				data-testid="select-mock-trigger"
			>
				セレクト
			</button>
			{children}
		</div>
	),
	SelectTrigger: ({
		id,
		children,
	}: {
		id?: string;
		children: React.ReactNode;
	}) => <div id={id}>{children}</div>,
	SelectValue: ({ placeholder }: { placeholder?: string }) => (
		<span>{placeholder}</span>
	),
	SelectContent: ({ children }: { children: React.ReactNode }) => (
		<div>{children}</div>
	),
	SelectItem: ({
		value,
		children,
	}: {
		value: string;
		children: React.ReactNode;
	}) => <div data-value={value}>{children}</div>,
}));

const baseVenues = [
	{ id: 1, name: "東京" },
	{ id: 2, name: "中山" },
];

const baseProps = {
	venues: baseVenues,
	onSubmit: vi.fn(),
};

describe("RaceInputForm", () => {
	beforeEach(() => {
		vi.clearAllMocks();
	});

	describe("レンダリング", () => {
		it("競馬場セレクト・レース日入力・レース番号セレクト・テキストエリアが表示される", () => {
			// Act
			render(<RaceInputForm {...baseProps} />);

			// Assert
			expect(screen.getByText("競馬場")).toBeInTheDocument();
			expect(screen.getByLabelText("レース日")).toBeInTheDocument();
			expect(screen.getByText("レース番号")).toBeInTheDocument();
			expect(screen.getByLabelText("出馬表をペースト")).toBeInTheDocument();
		});
	});

	describe("バリデーション", () => {
		it("全項目未入力時は「保存する」ボタンが disabled", () => {
			// Act
			render(<RaceInputForm {...baseProps} />);

			// Assert
			expect(screen.getByRole("button", { name: "保存する" })).toBeDisabled();
		});

		it("全項目入力済みで「保存する」ボタンが enabled になる", async () => {
			// Arrange
			const user = userEvent.setup();

			// Act
			render(<RaceInputForm {...baseProps} />);

			// 競馬場を選択（モックの "1" がセットされる）
			const selectButtons = screen.getAllByRole("button", { name: "セレクト" });
			await user.click(selectButtons[0]); // 競馬場

			// レース日を入力
			await user.type(screen.getByLabelText("レース日"), "2026-04-18");

			// レース番号を選択（モックの "1" がセットされる）
			await user.click(selectButtons[1]); // レース番号

			// テキストエリアに入力
			await user.type(
				screen.getByLabelText("出馬表をペースト"),
				"出馬表テキスト",
			);

			// Assert
			expect(
				screen.getByRole("button", { name: "保存する" }),
			).not.toBeDisabled();
		});
	});

	describe("インタラクション", () => {
		it("「保存する」クリック時に onSubmit が正しい引数で呼ばれる", async () => {
			// Arrange
			const onSubmit = vi.fn();
			const user = userEvent.setup();

			// Act
			render(<RaceInputForm {...baseProps} onSubmit={onSubmit} />);

			// 競馬場を選択
			const selectButtons = screen.getAllByRole("button", { name: "セレクト" });
			await user.click(selectButtons[0]);

			// レース日を入力
			await user.type(screen.getByLabelText("レース日"), "2026-04-18");

			// レース番号を選択
			await user.click(selectButtons[1]);

			// テキストエリアに入力
			await user.type(
				screen.getByLabelText("出馬表をペースト"),
				"出馬表テキスト",
			);

			// 保存する
			await user.click(screen.getByRole("button", { name: "保存する" }));

			// Assert
			expect(onSubmit).toHaveBeenCalledTimes(1);
			expect(onSubmit).toHaveBeenCalledWith({
				venue_id: 1,
				race_date: "2026-04-18",
				race_number: 1,
				paste_text: "出馬表テキスト",
			});
		});
	});

	describe("初期値", () => {
		it("initialVenueId が渡された場合、競馬場の初期値として反映される", () => {
			// Act
			render(<RaceInputForm {...baseProps} initialVenueId={2} />);

			// Assert: Select の data-value 属性で初期値を確認
			const selectDivs = screen
				.getAllByRole("button", { name: "セレクト" })
				.map((btn) => btn.closest("[data-value]"));
			expect(selectDivs[0]).toHaveAttribute("data-value", "2");
		});

		it("initialRaceDate が渡された場合、レース日の初期値として反映される", () => {
			// Act
			render(
				<RaceInputForm {...baseProps} initialRaceDate="2026-04-18" />,
			);

			// Assert
			expect(screen.getByLabelText("レース日")).toHaveValue("2026-04-18");
		});

		it("initialRaceNumber が渡された場合、レース番号の初期値として反映される", () => {
			// Act
			render(<RaceInputForm {...baseProps} initialRaceNumber={5} />);

			// Assert: Select の data-value 属性で初期値を確認
			const selectDivs = screen
				.getAllByRole("button", { name: "セレクト" })
				.map((btn) => btn.closest("[data-value]"));
			expect(selectDivs[1]).toHaveAttribute("data-value", "5");
		});
	});
});
