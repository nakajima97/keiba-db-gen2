import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";
import RaceNominationStatusToggle from "./index";

describe("RaceNominationStatusToggle", () => {
	it("未設定の状態で消しを押すと onChange に excluded が渡される", async () => {
		// Arrange
		const user = userEvent.setup();
		const onChange = vi.fn();
		render(
			<RaceNominationStatusToggle
				value={null}
				horseName="テストホース"
				onChange={onChange}
			/>,
		);

		// Act
		await user.click(screen.getByRole("radio", { name: "テストホースを消し" }));

		// Assert
		expect(onChange).toHaveBeenCalledWith("excluded");
	});

	it("選択中の項目をもう一度押すと onChange に null が渡される", async () => {
		// Arrange
		const user = userEvent.setup();
		const onChange = vi.fn();
		render(
			<RaceNominationStatusToggle
				value="check"
				horseName="テストホース"
				onChange={onChange}
			/>,
		);

		// Act
		await user.click(screen.getByRole("radio", { name: "テストホースをチェック" }));

		// Assert
		expect(onChange).toHaveBeenCalledWith(null);
	});
});
