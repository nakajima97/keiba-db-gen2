import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it } from "vitest";
import HorseNoteIconButtonContainer from "./index";

describe("HorseNoteIconButtonContainer", () => {
	it("ハッピーパス: メモ有り（race紐づき）のアイコンクリックで edit モードのモーダルが開く", async () => {
		// Arrange
		const user = userEvent.setup();
		render(
			<HorseNoteIconButtonContainer
				horseId={100}
				horseName="ディープスター"
				raceId={200}
				raceLabel="2026/04/19 東京 11R 皐月賞"
				initialNote={{
					id: 5,
					content: "前走は外枠で出遅れ気味。",
					source: "race",
				}}
			/>,
		);

		// Act
		await user.click(
			screen.getByRole("button", { name: "ディープスターのメモ" }),
		);

		// Assert
		expect(screen.getByRole("dialog")).toBeInTheDocument();
		expect(screen.getByText("メモを編集")).toBeInTheDocument();
		expect(
			screen.getByDisplayValue("前走は外枠で出遅れ気味。"),
		).toBeInTheDocument();
	});
});
