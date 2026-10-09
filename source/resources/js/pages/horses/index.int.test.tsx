import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";
import { createInertiaReactMock } from "@/tests/mocks";
import HorsesIndex from "./index";

const { routerGet } = vi.hoisted(() => ({ routerGet: vi.fn() }));

vi.mock("@inertiajs/react", () =>
	createInertiaReactMock({
		usePage: () => ({
			props: {
				horses: [{ uid: "horse001", name: "サンプルホース", birth_year: 2020 }],
				filters: { keyword: "サンプル" },
			},
		}),
		router: { get: routerGet },
	}),
);

vi.mock("@/routes/horses", () => ({
	index: { url: () => "/horses" },
	show: { url: ({ horse }: { horse: string }) => `/horses/${horse}` },
}));

describe("HorsesIndex ページ", () => {
	it("ハッピーパス: Inertia propsの検索結果が表示され、再検索でキーワード付きで遷移する", async () => {
		// Arrange
		const user = userEvent.setup();
		render(<HorsesIndex />);

		// Act
		const input = screen.getByLabelText("馬名");
		await user.clear(input);
		await user.type(input, " ホース ");
		await user.click(screen.getByRole("button", { name: "検索" }));

		// Assert
		expect(screen.getByRole("link", { name: "サンプルホース" })).toHaveAttribute(
			"href",
			"/horses/horse001",
		);
		expect(routerGet).toHaveBeenCalledWith(
			"/horses",
			{ keyword: "ホース" },
			{ preserveState: true },
		);
	});
});
