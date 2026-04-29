import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi, beforeEach } from "vitest";
import RaceResultFormContainer from "./index";

vi.mock("@inertiajs/react", () => ({
	router: {
		post: vi.fn(),
	},
	Link: ({
		href,
		children,
	}: {
		href: string;
		children: React.ReactNode;
	}) => <a href={href}>{children}</a>,
}));

import { router } from "@inertiajs/react";

const defaultProps = {
	raceUid: "test-uid-123",
	venueName: "東京",
	raceDate: "2026-04-05",
	raceNumber: 1,
};

describe("RaceResultFormContainer", () => {
	beforeEach(() => {
		vi.clearAllMocks();
	});

	it("ハッピーパス: 着順・払い戻しテキスト入力後に保存ボタンをクリックすると router.post が正しいURLとデータで呼ばれる", async () => {
		// Arrange
		const user = userEvent.setup();
		render(<RaceResultFormContainer {...defaultProps} />);

		const resultTextarea = screen.getByLabelText("着順情報をペースト");
		const payoutTextarea = screen.getByLabelText("払い戻し情報をペースト");

		// Act
		await user.type(resultTextarea, "sample result text");
		await user.type(payoutTextarea, "sample payout text");
		await user.click(screen.getByRole("button", { name: "保存する" }));

		// Assert
		expect(router.post).toHaveBeenCalledTimes(1);
		expect(router.post).toHaveBeenCalledWith(
			expect.stringContaining("test-uid-123"),
			expect.objectContaining({
				result_text: "sample result text",
				text: "sample payout text",
			}),
			expect.any(Object),
		);
	});
});
