import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi } from "vitest";
import RaceList from "./index";
import type { RaceListProps } from "./types";

vi.mock("@inertiajs/react", () => ({
	Link: ({ href, children }: { href: string; children: unknown }) => (
		<a href={href}>{children as never}</a>
	),
	router: {
		visit: vi.fn(),
	},
}));

vi.mock("@/routes/races", () => ({
	create: {
		url: () => "/races/new",
	},
	show: {
		url: ({ race }: { race: string }) => `/races/${race}`,
	},
}));

vi.mock("@/components/shadcn/ui/button", () => ({
	Button: ({
		variant,
		onClick,
		children,
	}: {
		variant?: string;
		onClick?: () => void;
		children: unknown;
	}) => (
		<button data-variant={variant ?? "default"} onClick={onClick} type="button">
			{children as never}
		</button>
	),
}));

const noop = () => {};

const baseVenues = [
	{ id: 1, name: "東京" },
	{ id: 2, name: "中山" },
];

const sampleRace = {
	uid: "abc123",
	race_date: "2026-04-05",
	venue_name: "東京",
	race_number: 1,
};

const baseProps: RaceListProps = {
	races: [sampleRace],
	venues: baseVenues,
	selectedDate: "",
	selectedVenueId: "all",
	onDateChange: noop,
	onVenueChange: noop,
};

describe("RaceList", () => {
	describe("空の状態", () => {
		it("「レースが見つかりません」が表示される", () => {
			// Act
			render(<RaceList {...baseProps} races={[]} />);

			// Assert
			expect(screen.getByText("レースが見つかりません")).toBeInTheDocument();
		});

		it("「レース情報入力」リンクが表示される", () => {
			// Act
			render(<RaceList {...baseProps} races={[]} />);

			// Assert（ヘッダーと空状態の両方に表示されるため複数件存在する）
			expect(
				screen.getAllByText("レース情報入力").length,
			).toBeGreaterThanOrEqual(1);
		});

		it("テーブルが表示されない", () => {
			// Act
			render(<RaceList {...baseProps} races={[]} />);

			// Assert
			expect(screen.queryByRole("table")).not.toBeInTheDocument();
		});
	});

	describe("データあり", () => {
		it("テーブルヘッダーが表示される（日付・開催場所・レース番号）", () => {
			// Act
			render(<RaceList {...baseProps} />);

			// Assert（フィルタのLabelと重複するためcolumnheaderロールで限定）
			expect(
				screen.getByRole("columnheader", { name: "日付" }),
			).toBeInTheDocument();
			expect(
				screen.getByRole("columnheader", { name: "開催場所" }),
			).toBeInTheDocument();
			expect(
				screen.getByRole("columnheader", { name: "レース番号" }),
			).toBeInTheDocument();
		});

		it("race_date が YYYY/MM/DD 形式で表示される（2026-04-05 → 2026/04/05）", () => {
			// Act
			render(<RaceList {...baseProps} />);

			// Assert
			expect(screen.getByText("2026/04/05")).toBeInTheDocument();
		});

		it("venue_name が表示される", () => {
			// Act
			render(<RaceList {...baseProps} />);

			// Assert（会場ボタンと重複するためテーブルセルで限定）
			expect(screen.getByRole("cell", { name: "東京" })).toBeInTheDocument();
		});

		it("race_number が「1R」形式で表示される", () => {
			// Act
			render(<RaceList {...baseProps} />);

			// Assert
			expect(screen.getByText("1R")).toBeInTheDocument();
		});

		it("ヘッダー右上に「レース情報入力」リンクが表示される", () => {
			// Act
			render(<RaceList {...baseProps} />);

			// Assert
			expect(screen.getByText("レース情報入力")).toBeInTheDocument();
		});

		it("「レース情報入力」リンクの遷移先が /races/new である", () => {
			// Act
			render(<RaceList {...baseProps} />);

			// Assert
			const link = screen.getByText("レース情報入力");
			expect(link).toHaveAttribute("href", "/races/new");
		});
	});

	describe("開催場所ボタン", () => {
		it("venues の数だけ会場ボタンが表示される（「すべて」を含む）", () => {
			// Act
			render(<RaceList {...baseProps} />);

			// Assert
			expect(
				screen.getByRole("button", { name: "すべて" }),
			).toBeInTheDocument();
			expect(screen.getByRole("button", { name: "東京" })).toBeInTheDocument();
			expect(screen.getByRole("button", { name: "中山" })).toBeInTheDocument();
		});

		it('selectedVenueId="all" のとき「すべて」ボタンが選択状態（data-variant="default"）になる', () => {
			// Act
			render(<RaceList {...baseProps} selectedVenueId="all" />);

			// Assert
			const allButton = screen.getByRole("button", { name: "すべて" });
			expect(allButton).toHaveAttribute("data-variant", "default");
		});

		it('selectedVenueId が会場IDのとき、対応する会場ボタンが選択状態（data-variant="default"）になる', () => {
			// Act
			render(<RaceList {...baseProps} selectedVenueId="1" />);

			// Assert
			const tokyoButton = screen.getByRole("button", { name: "東京" });
			expect(tokyoButton).toHaveAttribute("data-variant", "default");
		});

		it('selectedVenueId が会場IDのとき「すべて」ボタンは非選択状態（data-variant="outline"）になる', () => {
			// Act
			render(<RaceList {...baseProps} selectedVenueId="1" />);

			// Assert
			const allButton = screen.getByRole("button", { name: "すべて" });
			expect(allButton).toHaveAttribute("data-variant", "outline");
		});

		it('「すべて」ボタンをクリックすると onVenueChange("all") が呼ばれる', async () => {
			// Arrange
			const onVenueChange = vi.fn();
			const user = userEvent.setup();

			// Act
			render(<RaceList {...baseProps} onVenueChange={onVenueChange} />);
			await user.click(screen.getByRole("button", { name: "すべて" }));

			// Assert
			expect(onVenueChange).toHaveBeenCalledTimes(1);
			expect(onVenueChange).toHaveBeenCalledWith("all");
		});

		it("会場ボタンをクリックすると onVenueChange(会場IDの文字列) が呼ばれる", async () => {
			// Arrange
			const onVenueChange = vi.fn();
			const user = userEvent.setup();

			// Act
			render(<RaceList {...baseProps} onVenueChange={onVenueChange} />);
			await user.click(screen.getByRole("button", { name: "東京" }));

			// Assert
			expect(onVenueChange).toHaveBeenCalledTimes(1);
			expect(onVenueChange).toHaveBeenCalledWith("1");
		});
	});

	describe("日付フィルタ", () => {
		it("日付入力フィールドが表示される", () => {
			// Act
			render(<RaceList {...baseProps} />);

			// Assert
			expect(screen.getByLabelText("日付")).toBeInTheDocument();
		});

		it("selectedDate の値が日付入力フィールドに反映される", () => {
			// Act
			render(<RaceList {...baseProps} selectedDate="2026-04-05" />);

			// Assert
			expect(screen.getByLabelText("日付")).toHaveValue("2026-04-05");
		});

		it("日付を変更すると onDateChange が呼ばれる", async () => {
			// Arrange
			const onDateChange = vi.fn();
			const user = userEvent.setup();

			// Act
			render(<RaceList {...baseProps} onDateChange={onDateChange} />);
			await user.type(screen.getByLabelText("日付"), "2026-04-10");

			// Assert
			expect(onDateChange).toHaveBeenCalled();
		});
	});

	describe("レース行クリック", () => {
		it("レースの行をクリックすると router.visit が show.url({ race: uid }) で呼ばれる", async () => {
			// Arrange
			const { router } = await import("@inertiajs/react");
			const user = userEvent.setup();

			// Act
			render(<RaceList {...baseProps} />);
			await user.click(screen.getByRole("row", { name: /2026\/04\/05/ }));

			// Assert
			expect(router.visit).toHaveBeenCalledWith("/races/abc123");
		});
	});
});
