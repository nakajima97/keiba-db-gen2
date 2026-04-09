import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi } from "vitest";
import RaceResultForm from "./index";

const baseProps = {
	venueName: "東京",
	raceDate: "2026-04-05",
	raceNumber: 1,
	pasteValue: "",
	onPasteChange: vi.fn(),
	parseError: null,
	onSubmit: vi.fn(),
	isSubmitting: false,
};

describe("RaceResultForm", () => {
	describe("レンダリング", () => {
		it("テキストエリアが表示される", () => {
			// Act
			render(<RaceResultForm {...baseProps} />);

			// Assert
			expect(screen.getByRole("textbox")).toBeInTheDocument();
		});

		it("送信ボタン「保存する」が表示される", () => {
			// Act
			render(<RaceResultForm {...baseProps} />);

			// Assert
			expect(
				screen.getByRole("button", { name: "保存する" }),
			).toBeInTheDocument();
		});

		it("parseError propが渡されたときにエラーメッセージが表示される", () => {
			// Arrange
			const props = {
				...baseProps,
				parseError: "データ形式が正しくありません",
			};

			// Act
			render(<RaceResultForm {...props} />);

			// Assert
			expect(screen.getByRole("alert")).toBeInTheDocument();
			expect(
				screen.getAllByText("データ形式が正しくありません").length,
			).toBeGreaterThanOrEqual(1);
		});

		it("parseError propがnullのときエラーメッセージは表示されない", () => {
			// Act
			render(<RaceResultForm {...baseProps} parseError={null} />);

			// Assert
			expect(screen.queryByRole("alert")).not.toBeInTheDocument();
		});
	});

	describe("インタラクション", () => {
		it("送信ボタンをクリックすると onSubmit propが呼ばれる", async () => {
			// Arrange
			const onSubmit = vi.fn();
			const user = userEvent.setup();

			// Act
			render(
				<RaceResultForm
					{...baseProps}
					pasteValue="単勝\t3\t610円\t2番人気"
					onSubmit={onSubmit}
				/>,
			);
			await user.click(screen.getByRole("button", { name: "保存する" }));

			// Assert
			expect(onSubmit).toHaveBeenCalledTimes(1);
		});

		it("テキストエリアが空のとき送信ボタンは無効になる", () => {
			// Act
			render(<RaceResultForm {...baseProps} pasteValue="" />);

			// Assert
			expect(screen.getByRole("button", { name: "保存する" })).toBeDisabled();
		});

		it("isSubmitting が true のとき「保存中...」ボタンが表示され無効になる", () => {
			// Act
			render(
				<RaceResultForm
					{...baseProps}
					pasteValue="some text"
					isSubmitting={true}
				/>,
			);

			// Assert
			expect(screen.getByRole("button", { name: "保存中..." })).toBeDisabled();
		});
	});
});
