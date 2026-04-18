import { render, screen } from "@testing-library/react";
import { describe, it, expect, vi } from "vitest";
import RacesShow from "./show";

vi.mock("@inertiajs/react", () => ({
	Head: ({ title }: { title: string }) => <title>{title}</title>,
	usePage: () => ({
		props: {
			race: {
				uid: "abc123",
				race_date: "2026-04-05",
				venue_name: "東京",
				race_number: 3,
				entries: [
					{
						frame_number: 1,
						horse_number: 1,
						horse_name: "テストホース",
						jockey_name: "テスト騎手",
						weight: 480,
					},
				],
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
