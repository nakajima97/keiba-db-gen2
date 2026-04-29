import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";
import HorseNotesListContainer from "./index";

vi.mock("@inertiajs/react", () => ({
	Link: ({ href, children }: { href: string; children: unknown }) => (
		<a href={href}>{children as never}</a>
	),
}));

describe("HorseNotesListContainer", () => {
	it("ハッピーパス: メモ追加ボタンを押すとモーダルが create モードで開く", async () => {
		// Arrange
		const user = userEvent.setup();
		render(
			<HorseNotesListContainer
				horseId={100}
				horseName="ディープスター"
				initialNotes={[
					{
						id: 10,
						content: "既存メモ",
						race: null,
						created_at: "2026-04-25T10:00:00Z",
						updated_at: "2026-04-25T10:00:00Z",
					},
				]}
				raceOptions={[]}
			/>,
		);

		// Act
		await user.click(screen.getByRole("button", { name: "メモを追加" }));

		// Assert
		expect(screen.getByRole("dialog")).toBeInTheDocument();
		expect(
			screen.getByRole("heading", { name: "メモを追加" }),
		).toBeInTheDocument();
		expect(screen.getByText("ディープスター")).toBeInTheDocument();
	});
});
