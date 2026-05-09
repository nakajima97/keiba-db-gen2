import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { createInertiaCoreMock } from "@/tests/mocks";
import RaceEntryAddFormContainer from "./index";

vi.mock("@inertiajs/core", async () => {
	const actual =
		await vi.importActual<typeof import("@inertiajs/core")>("@inertiajs/core");
	return createInertiaCoreMock({ actual });
});

import { router } from "@inertiajs/core";

const defaultProps = {
	raceUid: "abc123uid",
	raceInfo: {
		race_date: "2026-04-26",
		venue_name: "東京",
		race_number: 11,
	},
};

describe("RaceEntryAddFormContainer", () => {
	beforeEach(() => {
		vi.clearAllMocks();
	});

	it("ハッピーパス: フォームを入力して「追加」ボタンをクリックすると router.post が正しい URL・データで呼ばれる", async () => {
		// Arrange
		const user = userEvent.setup();
		render(<RaceEntryAddFormContainer {...defaultProps} />);

		await user.type(screen.getByLabelText("馬名"), "コントレイル");
		await user.type(screen.getByLabelText("騎手名"), "ルメール");
		await user.type(screen.getByLabelText("枠番（1〜8）"), "3");
		await user.type(screen.getByLabelText("馬番（1〜18）"), "5");
		await user.type(screen.getByLabelText("負担重量（kg）"), "57.0");
		await user.type(screen.getByLabelText("馬体重（kg、任意）"), "486");

		// Act
		await user.click(screen.getByRole("button", { name: "追加" }));

		// Assert
		expect(router.post).toHaveBeenCalledTimes(1);
		expect(router.post).toHaveBeenCalledWith(
			expect.stringContaining("races/abc123uid/entries/add"),
			expect.objectContaining({
				horse_name: "コントレイル",
				jockey_name: "ルメール",
				frame_number: 3,
				horse_number: 5,
				weight: "57.0",
				horse_weight: "486",
			}),
			expect.any(Object),
		);
	});
});
