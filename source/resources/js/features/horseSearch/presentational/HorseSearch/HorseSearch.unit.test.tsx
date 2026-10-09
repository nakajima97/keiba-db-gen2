import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";
import HorseSearch from "./index";
import type { HorseSearchProps } from "./types";

vi.mock("@inertiajs/react", () => ({
	Link: ({ href, children }: { href: string; children: unknown }) => (
		<a href={href}>{children as never}</a>
	),
}));

vi.mock("@/routes/horses", () => ({
	show: {
		url: ({ horse }: { horse: string }) => `/horses/${horse}`,
	},
}));

const noop = () => {};

const baseProps: HorseSearchProps = {
	horses: [],
	keyword: "",
	searchedKeyword: null,
	onKeywordChange: noop,
	onSearch: noop,
};

describe("HorseSearch", () => {
	it("未検索の場合は検索を促すメッセージが表示される", () => {
		// Act
		render(<HorseSearch {...baseProps} />);

		// Assert
		expect(
			screen.getByText("馬名を入力して検索してください"),
		).toBeInTheDocument();
	});

	it("検索結果が0件の場合は該当なしのメッセージが表示される", () => {
		// Act
		render(<HorseSearch {...baseProps} searchedKeyword="ホース" />);

		// Assert
		expect(
			screen.getByText("該当する競走馬が見つかりません"),
		).toBeInTheDocument();
	});

	it("検索結果の馬名が競走馬詳細へのリンクとして表示され、生年が表示される", () => {
		// Arrange
		const horses = [
			{ uid: "horse001", name: "サンプルホース", birth_year: 2020 },
			{ uid: "horse002", name: "セイネンフメイ", birth_year: null },
		];

		// Act
		render(
			<HorseSearch {...baseProps} horses={horses} searchedKeyword="ホース" />,
		);

		// Assert
		expect(screen.getByRole("link", { name: "サンプルホース" })).toHaveAttribute(
			"href",
			"/horses/horse001",
		);
		expect(screen.getByText("2020年")).toBeInTheDocument();
		expect(screen.getByText("—")).toBeInTheDocument();
	});

	it("馬名を入力すると onKeywordChange が呼ばれる", async () => {
		// Arrange
		const onKeywordChange = vi.fn();
		const user = userEvent.setup();
		render(<HorseSearch {...baseProps} onKeywordChange={onKeywordChange} />);

		// Act
		await user.type(screen.getByLabelText("馬名"), "ア");

		// Assert
		expect(onKeywordChange).toHaveBeenCalledWith("ア");
	});

	it("検索ボタンを押すと onSearch が呼ばれる", async () => {
		// Arrange
		const onSearch = vi.fn();
		const user = userEvent.setup();
		render(<HorseSearch {...baseProps} onSearch={onSearch} />);

		// Act
		await user.click(screen.getByRole("button", { name: "検索" }));

		// Assert
		expect(onSearch).toHaveBeenCalledTimes(1);
	});
});
