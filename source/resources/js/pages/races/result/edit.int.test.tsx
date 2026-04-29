import { render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import RaceResultEdit from "./edit";

vi.mock("@inertiajs/react", () => ({
	Head: ({ title }: { title: string }) => <title>{title}</title>,
	Link: ({ href, children }: { href: string; children: unknown }) => (
		<a href={href}>{children as never}</a>
	),
	usePage: () => ({
		props: {
			race: {
				id: 200,
				uid: "abc123",
				venue_name: "東京",
				race_date: "2026-04-05",
				race_number: 3,
				race_name: null,
				payouts: [],
				finishing_horses: [
					{
						finishing_order: 1,
						frame_number: 1,
						horse_number: 1,
						horse_id: 100,
						horse_name: "テストホース",
						jockey_name: "テスト騎手",
						race_time: "1:34.5",
						note: {
							id: 5,
							content: "前走は出遅れ気味",
							source: "race",
						},
					},
				],
			},
		},
	}),
}));

describe("RaceResultEdit ページ", () => {
	it("ハッピーパス: Inertia propsのデータが RaceResultDetail に表示され、メモセルが描画される", () => {
		// Act
		render(<RaceResultEdit />);

		// Assert
		expect(screen.getByText("レース結果")).toBeInTheDocument();
		expect(screen.getByText("テストホース")).toBeInTheDocument();
		expect(screen.getByText("前走は出遅れ気味")).toBeInTheDocument();
	});
});
