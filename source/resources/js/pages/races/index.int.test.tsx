import { render, screen } from "@testing-library/react";
import { describe, it, expect, vi } from "vitest";
import RacesIndex from "./index";

vi.mock("@inertiajs/react", () => ({
	Head: ({ title }: { title: string }) => <title>{title}</title>,
	usePage: () => ({
		props: {
			races: [
				{
					uid: "abc123",
					race_date: "2026-04-05",
					venue_name: "東京",
					race_number: 1,
				},
			],
			venues: [{ id: 1, name: "東京" }],
			filters: {
				race_date: "2026-04-05",
				venue_id: null,
			},
		},
	}),
	router: {
		get: vi.fn(),
	},
	Link: ({ href, children }: { href: string; children: unknown }) => (
		<a href={href}>{children as never}</a>
	),
}));

vi.mock("@/routes/races", () => ({
	create: { url: () => "/races/new" },
	index: { url: () => "/races" },
}));

describe("RacesIndex ページ", () => {
	it("ハッピーパス: Inertia propsのデータがRaceListに表示される", () => {
		// Act
		render(<RacesIndex />);

		// Assert
		expect(screen.getByText("レース一覧")).toBeInTheDocument();
		expect(screen.getByText("2026/04/05")).toBeInTheDocument();
		expect(screen.getAllByText("東京").length).toBeGreaterThanOrEqual(1);
		expect(screen.getByText("1R")).toBeInTheDocument();
	});
});
