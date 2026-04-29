import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi } from "vitest";
import BackButton from "./index";

vi.mock("@inertiajs/react", () => ({
	Link: ({
		href,
		children,
	}: {
		href: string;
		children: React.ReactNode;
	}) => <a href={href}>{children}</a>,
}));

describe("BackButton", () => {
	describe("href が指定されているとき", () => {
		it("リンク要素としてレンダリングされる", () => {
			// Act
			render(<BackButton label="戻る" href="/races" />);

			// Assert
			expect(screen.getByRole("link")).toBeInTheDocument();
		});

		it("リンクの href 属性に指定した値が設定されている", () => {
			// Act
			render(<BackButton label="戻る" href="/races" />);

			// Assert
			expect(screen.getByRole("link")).toHaveAttribute("href", "/races");
		});

		it("children として渡したラベルが表示される", () => {
			// Act
			render(<BackButton label="レース一覧へ戻る" href="/races" />);

			// Assert
			expect(screen.getByText("レース一覧へ戻る")).toBeInTheDocument();
		});
	});

	describe("href が指定されていないとき", () => {
		it("ボタン要素としてレンダリングされる", () => {
			// Act
			render(<BackButton label="戻る" />);

			// Assert
			expect(screen.getByRole("button", { name: "戻る" })).toBeInTheDocument();
		});

		it("クリックすると window.history.back が呼ばれる", async () => {
			// Arrange
			const backSpy = vi
				.spyOn(window.history, "back")
				.mockImplementation(() => {});
			const user = userEvent.setup();

			// Act
			render(<BackButton label="戻る" />);
			await user.click(screen.getByRole("button", { name: "戻る" }));

			// Assert
			expect(backSpy).toHaveBeenCalledTimes(1);
			backSpy.mockRestore();
		});

		it("ラベルが表示される", () => {
			// Act
			render(<BackButton label="戻る" />);

			// Assert
			expect(screen.getByText("戻る")).toBeInTheDocument();
		});
	});
});
