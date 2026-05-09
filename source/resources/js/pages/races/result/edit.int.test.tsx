import { render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { createInertiaReactMock } from "@/tests/mocks";
import RaceResultEdit from "./edit";

vi.mock("@inertiajs/react", () =>
	createInertiaReactMock({
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
							horse_uid: "horse-uid-100",
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
				tickets: [
					{
						id: 1,
						ticket_type_label: "馬連",
						buy_type_name: "box",
						buy_type_label: "ボックス",
						selections: { horses: [1, 3, 5] },
						purchase_amount: 3000,
						payout_amount: 700,
					},
				],
			},
		}),
	}),
);

describe("RaceResultEdit ページ", () => {
	it("ハッピーパス: Inertia propsのデータが RaceResultDetail に表示され、メモセルが描画される", () => {
		// Act
		render(<RaceResultEdit />);

		// Assert
		expect(screen.getByText("レース結果")).toBeInTheDocument();
		expect(screen.getByText("テストホース")).toBeInTheDocument();
		expect(screen.getByText("前走は出遅れ気味")).toBeInTheDocument();
	});

	it("tickets が渡されたとき「自分の購入馬券」セクションが描画される", () => {
		// Act
		render(<RaceResultEdit />);

		// Assert
		expect(
			screen.getByRole("heading", { name: "自分の購入馬券" }),
		).toBeInTheDocument();
		expect(screen.getByText("ボックス")).toBeInTheDocument();
	});
});
