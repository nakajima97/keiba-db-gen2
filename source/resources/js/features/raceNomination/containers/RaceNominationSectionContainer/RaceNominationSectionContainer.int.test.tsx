import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";

vi.mock("@inertiajs/react", () => ({
	Link: ({ href, children }: { href: string; children: React.ReactNode }) => (
		<a href={href}>{children}</a>
	),
}));
vi.mock("@/features/raceNomination/requests/raceNominations", () => ({
	searchHorses: vi.fn().mockResolvedValue([]),
}));

import RaceNominationSectionContainer from "./index";

describe("RaceNominationSectionContainer", () => {
	it("ハッピーパス: 馬名を入力して Enter で onAdd が呼ばれ、成功すると入力欄が空になる", async () => {
		// Arrange
		const user = userEvent.setup();
		const onAdd = vi.fn().mockResolvedValue({ ok: true });
		render(
			<RaceNominationSectionContainer
				nominations={[]}
				onAdd={onAdd}
				onRemove={vi.fn()}
				onStatusChange={vi.fn()}
				onNoteClick={vi.fn()}
			/>,
		);
		const input = screen.getByLabelText("登録馬の馬名");

		// Act
		await user.type(input, " ウワサノホース {Enter}");

		// Assert
		expect(onAdd).toHaveBeenCalledWith({ horseName: "ウワサノホース" });
		expect(input).toHaveValue("");
	});
});
