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

describe("RaceResultDetail", () => {
	it("「レース結果入力」リンクが表示される", () => {
		// Act
		render(<RaceResultDetail {...baseProps} />);

		// Assert
		expect(
			screen.getByRole("link", { name: "レース結果入力" }),
		).toBeInTheDocument();
	});

	it("「レース結果入力」リンクのhrefにraceのuidを含む/result/newパスが設定されている", () => {
		// Act
		render(<RaceResultDetail {...baseProps} />);

		// Assert
		const link = screen.getByRole("link", { name: "レース結果入力" });
		expect(link).toHaveAttribute(
			"href",
			"/races/test-uid-123/result/new",
		);
	});
});
