import { render, screen } from "@testing-library/react";
import { describe, it, expect, vi } from "vitest";
import RaceEntryRegistrationForm from "./index";
import type { RaceEntryRegistrationFormProps } from "./types";

vi.mock("@inertiajs/react", () => ({
	Link: ({
		href,
		children,
	}: {
		href: string;
		children: React.ReactNode;
	}) => <a href={href}>{children}</a>,
}));

const baseProps: RaceEntryRegistrationFormProps = {
	raceUid: "test-race-uid",
	raceInfo: {
		race_date: "2026-04-05",
		venue_name: "東京",
		race_number: 1,
	},
	pastedText: "",
	isSubmitting: false,
	onPastedTextChange: vi.fn(),
	onSubmit: vi.fn(),
};

describe("RaceEntryRegistrationForm", () => {
	describe("戻るボタン", () => {
		it("raceUid prop が渡されたとき「レース詳細へ戻る」テキストのリンクが表示される", () => {
			// Act
			render(<RaceEntryRegistrationForm {...baseProps} />);

			// Assert
			expect(
				screen.getByRole("link", { name: "レース詳細へ戻る" }),
			).toBeInTheDocument();
		});

		it("「レース詳細へ戻る」リンクの href が `/races/{raceUid}` になっている", () => {
			// Act
			render(<RaceEntryRegistrationForm {...baseProps} />);

			// Assert
			const link = screen.getByRole("link", { name: "レース詳細へ戻る" });
			expect(link).toHaveAttribute("href", "/races/test-race-uid");
		});
	});
});
