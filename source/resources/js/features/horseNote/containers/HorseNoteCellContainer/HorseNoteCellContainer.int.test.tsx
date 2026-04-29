import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it } from "vitest";
import HorseNoteCellContainer from "./index";

describe("HorseNoteCellContainer", () => {
	it("ハッピーパス: メモなしのセルクリックで create モードのモーダルが開く", async () => {
		// Arrange
		const user = userEvent.setup();
		render(
			<HorseNoteCellContainer
				horseId={100}
				horseName="ディープスター"
				raceId={200}
				raceLabel="2026/04/19 東京 11R 皐月賞"
				initialNote={null}
			/>,
		);

		// Act
		await user.click(screen.getByRole("button", { name: "+ メモを追加" }));

		// Assert
		expect(screen.getByRole("dialog")).toBeInTheDocument();
		expect(screen.getByText("メモを追加")).toBeInTheDocument();
	});
});
