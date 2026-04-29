import { render, screen } from "@testing-library/react";
import { describe, it, expect, vi } from "vitest";
import RaceResultDetail from "./index";

vi.mock("@inertiajs/react", () => ({
	Link: ({
		href,
		children,
	}: {
		href: string;
		children: React.ReactNode;
	}) => <a href={href}>{children}</a>,
}));

const baseProps = {
	race: {
		uid: "test-uid-123",
		venue_name: "東京",
		race_date: "2026-04-05",
		race_number: 1,
		payouts: [],
		finishing_horses: [],
	},
};

const sampleFinishingHorse = {
	horse_id: 7,
	finishing_order: 1,
	frame_number: 2,
	horse_number: 3,
	horse_name: "テスト馬A",
	jockey_name: "騎手A",
	race_time: "1:34.5",
};

describe("RaceResultDetail", () => {
	it("着順データがないとき「レース結果入力」リンクが表示される", () => {
		// Act
		render(<RaceResultDetail {...baseProps} />);

		// Assert
		expect(
			screen.getByRole("link", { name: "レース結果入力" }),
		).toBeInTheDocument();
	});

	it("着順データがないとき「レース結果入力」リンクのhrefにraceのuidを含む/result/newパスが設定されている", () => {
		// Act
		render(<RaceResultDetail {...baseProps} />);

		// Assert
		const link = screen.getByRole("link", { name: "レース結果入力" });
		expect(link).toHaveAttribute(
			"href",
			"/races/test-uid-123/result/new",
		);
	});

	it("着順データがあるとき「レース結果入力」リンクが表示されない", () => {
		// Arrange
		const props = {
			race: {
				...baseProps.race,
				finishing_horses: [sampleFinishingHorse],
			},
		};

		// Act
		render(<RaceResultDetail {...props} />);

		// Assert
		expect(
			screen.queryByRole("link", { name: "レース結果入力" }),
		).not.toBeInTheDocument();
	});

	it("着順テーブルの馬名が競走馬詳細ページへのリンクとして表示される", () => {
		// Arrange
		const props = {
			race: {
				...baseProps.race,
				finishing_horses: [sampleFinishingHorse],
			},
		};

		// Act
		render(<RaceResultDetail {...props} />);

		// Assert
		const link = screen.getByRole("link", { name: "テスト馬A" });
		expect(link).toHaveAttribute("href", "/horses/7");
	});

	describe("戻るボタン", () => {
		it("「購入馬券一覧へ戻る」テキストのリンクが表示される", () => {
			// Act
			render(<RaceResultDetail {...baseProps} />);

			// Assert
			expect(
				screen.getByRole("link", { name: "購入馬券一覧へ戻る" }),
			).toBeInTheDocument();
		});

		it("「購入馬券一覧へ戻る」リンクの href が `/tickets` になっている", () => {
			// Act
			render(<RaceResultDetail {...baseProps} />);

			// Assert
			const link = screen.getByRole("link", { name: "購入馬券一覧へ戻る" });
			expect(link).toHaveAttribute("href", "/tickets");
		});
	});
});
