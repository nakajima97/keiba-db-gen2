import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import RaceEntryRegistrationFormContainer from "./index";

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
	raceUid: "abc123uid",
	raceInfo: {
		race_date: "2026-04-26",
		venue_name: "東京",
		race_number: 11,
	},
};

describe("RaceEntryRegistrationFormContainer", () => {
	beforeEach(() => {
		vi.clearAllMocks();
	});

	it("ハッピーパス: 登録ボタンをクリックすると router.post が /races/{uid}/entries に呼ばれる", async () => {
		// Arrange
		const user = userEvent.setup();
		vi.mocked(router.post).mockImplementation((_url, _data, options) => {
			options?.onSuccess?.({} as never);
		});
		render(<RaceEntryRegistrationFormContainer {...defaultProps} />);

		const textarea = screen.getByLabelText("JRA出馬表テキスト");
		await user.type(textarea, "sample paste text");

		// Act
		await user.click(screen.getByRole("button", { name: "登録" }));

		// Assert
		expect(router.post).toHaveBeenCalledTimes(1);
		expect(router.post).toHaveBeenCalledWith(
			expect.stringContaining("races/abc123uid/entries"),
			expect.objectContaining({
				paste_text: "sample paste text",
			}),
			expect.objectContaining({
				onSuccess: expect.any(Function),
			}),
		);
	});
});
