import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { createInertiaCoreMock } from "@/tests/mocks";
import RaceEntryEditFormContainer from "./index";

vi.mock("@inertiajs/core", async () => {
	const actual =
		await vi.importActual<typeof import("@inertiajs/core")>("@inertiajs/core");
	return createInertiaCoreMock({ actual });
});

import { router } from "@inertiajs/core";

const defaultProps = {
	raceUid: "abc123uid",
	entryUid: "entry42uid",
	raceInfo: {
		race_date: "2026-04-26",
		venue_name: "東京",
		race_number: 11,
	},
	initialValues: {
		horse_name: "コントレイル",
		jockey_name: "福永祐一",
		frame_number: 2,
		horse_number: 3,
		weight: "57.0",
		horse_weight: "486",
	},
};

describe("RaceEntryEditFormContainer", () => {
	beforeEach(() => {
		vi.clearAllMocks();
	});

	it("ハッピーパス: フォームを入力して「更新」ボタンをクリックすると router.put が正しい URL・データで呼ばれる", async () => {
		// Arrange
		const user = userEvent.setup();
		render(<RaceEntryEditFormContainer {...defaultProps} />);

		const horseNameInput = screen.getByLabelText("馬名");
		await user.clear(horseNameInput);
		await user.type(horseNameInput, "新しい馬名");

		// Act
		await user.click(screen.getByRole("button", { name: "更新" }));

		// Assert
		expect(router.put).toHaveBeenCalledTimes(1);
		expect(router.put).toHaveBeenCalledWith(
			expect.stringContaining("races/abc123uid/entries/entry42uid"),
			expect.objectContaining({
				horse_name: "新しい馬名",
				jockey_name: "福永祐一",
				frame_number: 2,
				horse_number: 3,
				weight: "57.0",
				horse_weight: "486",
			}),
			expect.any(Object),
		);
	});
});
