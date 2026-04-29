import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi } from "vitest";
import RaceResultForm from "./index";

vi.mock("@inertiajs/react", () => ({
	Link: ({
		href,
		children,
	}: {
		href: string;
		children: React.ReactNode;
	}) => <a href={href}>{children}</a>,
}));

const baseProps = {
	raceUid: "test-race-uid",
	venueName: "東京",
	raceDate: "2026-04-05",
	raceNumber: 1,
	resultPasteValue: "",
	onResultPasteChange: vi.fn(),
	resultParseError: null,
	payoutPasteValue: "",
	onPayoutPasteChange: vi.fn(),
	payoutParseError: null,
	onSubmit: vi.fn(),
	isSubmitting: false,
};

describe("RaceResultForm", () => {
	describe("レンダリング", () => {
		it("着順テキストエリアと払い戻しテキストエリアの2つが表示される", () => {
			// Act
			render(<RaceResultForm {...baseProps} />);

			// Assert
			expect(
				screen.getByLabelText("着順情報をペースト"),
			).toBeInTheDocument();
			expect(
				screen.getByLabelText("払い戻し情報をペースト"),
			).toBeInTheDocument();
		});

		it("送信ボタン「保存する」が表示される", () => {
			// Act
			render(<RaceResultForm {...baseProps} />);

			// Assert
			expect(
				screen.getByRole("button", { name: "保存する" }),
			).toBeInTheDocument();
		});

		it("resultParseError propが渡されたときにエラーメッセージが表示される", () => {
			// Arrange
			const props = {
				...baseProps,
				resultParseError: "着順データ形式が正しくありません",
			};

			// Act
			render(<RaceResultForm {...props} />);

			// Assert
			expect(screen.getByRole("alert")).toBeInTheDocument();
			expect(
				screen.getAllByText("着順データ形式が正しくありません").length,
			).toBeGreaterThanOrEqual(1);
		});

		it("payoutParseError propが渡されたときにエラーメッセージが表示される", () => {
			// Arrange
			const props = {
				...baseProps,
				payoutParseError: "払い戻しデータ形式が正しくありません",
			};

			// Act
			render(<RaceResultForm {...props} />);

			// Assert
			expect(screen.getByRole("alert")).toBeInTheDocument();
			expect(
				screen.getAllByText("払い戻しデータ形式が正しくありません").length,
			).toBeGreaterThanOrEqual(1);
		});

		it("parseError propがnullのときエラーメッセージは表示されない", () => {
			// Act
			render(
				<RaceResultForm
					{...baseProps}
					resultParseError={null}
					payoutParseError={null}
				/>,
			);

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
					resultPasteValue="some text"
					payoutPasteValue="some text"
					onSubmit={onSubmit}
				/>,
			);
			await user.click(screen.getByRole("button", { name: "保存する" }));

			// Assert
			expect(onSubmit).toHaveBeenCalledTimes(1);
		});

		it("resultPasteValueが空のとき送信ボタンは無効になる", () => {
			// Act
			render(
				<RaceResultForm
					{...baseProps}
					resultPasteValue=""
					payoutPasteValue="some text"
				/>,
			);

			// Assert
			expect(screen.getByRole("button", { name: "保存する" })).toBeDisabled();
		});

		it("payoutPasteValueが空のとき送信ボタンは無効になる", () => {
			// Act
			render(
				<RaceResultForm
					{...baseProps}
					resultPasteValue="some text"
					payoutPasteValue=""
				/>,
			);

			// Assert
			expect(screen.getByRole("button", { name: "保存する" })).toBeDisabled();
		});

		it("resultPasteValueとpayoutPasteValueが両方入力済みのとき送信ボタンは有効になる", () => {
			// Act
			render(
				<RaceResultForm
					{...baseProps}
					resultPasteValue="some text"
					payoutPasteValue="some text"
				/>,
			);

			// Assert
			expect(
				screen.getByRole("button", { name: "保存する" }),
			).not.toBeDisabled();
		});

		it("disabled=true のとき着順情報テキストエリアが disabled になる", () => {
			// Act
			render(<RaceResultForm {...baseProps} disabled={true} />);

			// Assert
			expect(screen.getByLabelText("着順情報をペースト")).toBeDisabled();
		});

		it("disabled=true のとき払い戻し情報テキストエリアが disabled になる", () => {
			// Act
			render(<RaceResultForm {...baseProps} disabled={true} />);

			// Assert
			expect(screen.getByLabelText("払い戻し情報をペースト")).toBeDisabled();
		});

		it("disabled=true かつ両テキストエリアに入力済みでも送信ボタンが disabled になる", () => {
			// Act
			render(
				<RaceResultForm
					{...baseProps}
					resultPasteValue="some text"
					payoutPasteValue="some text"
					disabled={true}
				/>,
			);

			// Assert
			expect(screen.getByRole("button", { name: "保存する" })).toBeDisabled();
		});

		it("isSubmitting が true のとき「保存中...」ボタンが表示され無効になる", () => {
			// Act
			render(
				<RaceResultForm
					{...baseProps}
					resultPasteValue="some text"
					payoutPasteValue="some text"
					isSubmitting={true}
				/>,
			);

			// Assert
			expect(screen.getByRole("button", { name: "保存中..." })).toBeDisabled();
		});
	});

	describe("戻るボタン", () => {
		it("「レース結果へ戻る」テキストのリンクが表示される", () => {
			// Act
			render(<RaceResultForm {...baseProps} />);

			// Assert
			expect(
				screen.getByRole("link", { name: "レース結果へ戻る" }),
			).toBeInTheDocument();
		});

		it("「レース結果へ戻る」リンクの href が `/races/{raceUid}/result/edit` になっている", () => {
			// Act
			render(<RaceResultForm {...baseProps} />);

			// Assert
			const link = screen.getByRole("link", { name: "レース結果へ戻る" });
			expect(link).toHaveAttribute(
				"href",
				"/races/test-race-uid/result/edit",
			);
		});
	});
});
