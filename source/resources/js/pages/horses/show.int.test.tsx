import { render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import HorsesShow from "./show";

vi.mock("@inertiajs/react", () => ({
	Head: ({ title }: { title: string }) => <title>{title}</title>,
	Link: ({ href, children }: { href: string; children: unknown }) => (
		<a href={href}>{children as never}</a>
	),
	usePage: () => ({
		props: {
			horse: {
				id: 100,
				name: "ディープスター",
				birth_year: 2022,
				race_histories: [
					{
						race_uid: "abc001",
						race_date: "2026-04-19",
						venue_name: "東京",
						race_number: 11,
						race_name: "皐月賞",
						finishing_order: 3,
						jockey_name: "テスト騎手",
						popularity: 5,
					},
				],
				notes: [
					{
						id: 1,
						content: "次走への備忘録",
						race: null,
						created_at: "2026-04-25T10:00:00Z",
						updated_at: "2026-04-25T10:00:00Z",
					},
				],
			},
		},
	}),
}));

describe("HorsesShow ページ", () => {
	it("ハッピーパス: Inertia propsのデータが HorseDetail と HorseNotesListContainer に表示される", () => {
		// Act
		render(<HorsesShow />);

		// Assert
		expect(screen.getByText("ディープスター")).toBeInTheDocument();
		expect(screen.getByText("2022年")).toBeInTheDocument();
		expect(screen.getByText("次走への備忘録")).toBeInTheDocument();
		expect(
			screen.getByRole("button", { name: "メモを追加" }),
		).toBeInTheDocument();
	});
});
