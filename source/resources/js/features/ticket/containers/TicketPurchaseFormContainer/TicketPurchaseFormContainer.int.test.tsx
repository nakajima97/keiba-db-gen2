import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi, beforeEach } from "vitest";
import TicketPurchaseFormContainer from "./index";

vi.mock("@inertiajs/react", () => ({
	router: {
		post: vi.fn(),
		visit: vi.fn(),
		reload: vi.fn(),
	},
}));

import { router } from "@inertiajs/react";

const defaultProps = {
	initialVenue: "東京",
	initialRaceDate: "2026-04-05",
	initialRaceNumber: 1,
	initialTicketTypeId: "umaren" as const,
	initialBuyTypeId: "nagashi",
	initialAxisCount: 1 as const,
	initialNagashiDirection: 1 as const,
	initialHorses: { axis: [3], others: [1, 5, 7] },
	initialUnitStake: 100,
};

describe("TicketPurchaseFormContainer", () => {
	beforeEach(() => {
		vi.clearAllMocks();
	});

	it("ハッピーパス: 登録ボタンをクリックすると router.post が呼ばれ、成功後に同じレース情報を引き継いで遷移する", async () => {
		// Arrange
		const user = userEvent.setup();

		vi.mocked(router.post).mockImplementation((_url, _data, options) => {
			options?.onSuccess?.({} as never);
		});

		render(<TicketPurchaseFormContainer {...defaultProps} />);

		const submitButton = screen.getByRole("button", { name: "登録する" });

		// Act
		await user.click(submitButton);

		// Assert
		expect(router.post).toHaveBeenCalledTimes(1);
		expect(router.post).toHaveBeenCalledWith(
			expect.stringContaining("tickets"),
			expect.objectContaining({
				venue: "東京",
				race_date: "2026-04-05",
				race_number: 1,
			}),
			expect.objectContaining({
				onSuccess: expect.any(Function),
			}),
		);
	});
});
