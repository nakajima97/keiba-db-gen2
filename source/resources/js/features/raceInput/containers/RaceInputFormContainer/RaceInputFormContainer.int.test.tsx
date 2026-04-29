import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import RaceInputFormContainer from "./index";

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
	venues: [
		{ id: 1, name: "東京" },
		{ id: 2, name: "中山" },
	],
	initialVenueId: 1,
	initialRaceDate: "2026-04-18",
	initialRaceNumber: 1,
};

describe("RaceInputFormContainer", () => {
	beforeEach(() => {
		vi.clearAllMocks();
	});

	it("ハッピーパス: 保存ボタンをクリックすると router.post が /races に呼ばれる", async () => {
		// Arrange
		const user = userEvent.setup();
		vi.mocked(router.post).mockImplementation((_url, _data, options) => {
			options?.onSuccess?.({} as never);
		});
		render(<RaceInputFormContainer {...defaultProps} />);

		const textarea = screen.getByLabelText("出馬表をペースト");
		await user.type(textarea, "sample paste text");

		// Act
		await user.click(screen.getByRole("button", { name: "保存する" }));

		// Assert
		expect(router.post).toHaveBeenCalledTimes(1);
		expect(router.post).toHaveBeenCalledWith(
			expect.stringContaining("races"),
			expect.objectContaining({
				venue_id: 1,
				race_date: "2026-04-18",
				race_number: 1,
				paste_text: "sample paste text",
			}),
			expect.objectContaining({
				onSuccess: expect.any(Function),
			}),
		);
	});

	it("ハッピーパス: race_name を入力して保存すると router.post の data に race_name が含まれる", async () => {
		// Arrange
		const user = userEvent.setup();
		vi.mocked(router.post).mockImplementation((_url, _data, options) => {
			options?.onSuccess?.({} as never);
		});
		render(<RaceInputFormContainer {...defaultProps} initialRaceName="天皇賞（春）" />);

		const textarea = screen.getByLabelText("出馬表をペースト");
		await user.type(textarea, "sample paste text");

		// Act
		await user.click(screen.getByRole("button", { name: "保存する" }));

		// Assert
		expect(router.post).toHaveBeenCalledWith(
			expect.stringContaining("races"),
			expect.objectContaining({ race_name: "天皇賞（春）" }),
			expect.anything(),
		);
	});
});
