import { render, screen } from "@testing-library/react";
import { describe, it, expect, vi } from "vitest";
import RacesShow from "./show";

vi.mock("@inertiajs/react", () => ({
	Head: ({ title }: { title: string }) => <title>{title}</title>,
	Link: ({ href, children }: { href: string; children: unknown }) => (
		<a href={href}>{children as never}</a>
	),
	usePage: () => ({
		props: {
			race: {
				uid: "abc123",
				race_date: "2026-04-05",
				venue_name: "東京",
				race_number: 3,
				entries: [
					{
						id: 1,
						frame_number: 1,
						horse_number: 1,
						horse_name: "テストホース",
						jockey_name: "テスト騎手",
						weight: 480,
					},
				],
				mark_columns: [
					{ id: 100, type: "own", label: null, display_order: 0 },
				],
				marks: [],
			},
		},
	}),
}));

describe("RacesShow ページ", () => {
	it("ハッピーパス: Inertia propsのデータがRaceDetailに表示される", () => {
		// Act
		render(<RacesShow />);

		// Assert
		expect(screen.getByText("2026/04/05")).toBeInTheDocument();
		expect(screen.getByText("東京")).toBeInTheDocument();
		expect(screen.getByText("3R")).toBeInTheDocument();
		expect(screen.getByText("テストホース")).toBeInTheDocument();
		expect(screen.getByText("テスト騎手")).toBeInTheDocument();
		expect(screen.getByText("480kg")).toBeInTheDocument();
	});
});
