import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi } from "vitest";
import RaceMarkSelect from "./index";

describe("RaceMarkSelect", () => {
	describe("選択肢の表示", () => {
		it("選択肢を開いたとき「―」（未選択）が表示される", async () => {
			// Arrange
			const user = userEvent.setup();

			// Act
			render(<RaceMarkSelect value={null} onChange={vi.fn()} />);
			await user.click(screen.getByRole("combobox"));

			// Assert
			expect(screen.getByRole("option", { name: "―" })).toBeInTheDocument();
		});

		it("選択肢を開いたとき 6 種の印（◎ ○ ▲ △ ☆ ✓）すべてが表示される", async () => {
			// Arrange
			const user = userEvent.setup();

			// Act
			render(<RaceMarkSelect value={null} onChange={vi.fn()} />);
			await user.click(screen.getByRole("combobox"));

			// Assert
			expect(screen.getByRole("option", { name: "◎" })).toBeInTheDocument();
			expect(screen.getByRole("option", { name: "○" })).toBeInTheDocument();
			expect(screen.getByRole("option", { name: "▲" })).toBeInTheDocument();
			expect(screen.getByRole("option", { name: "△" })).toBeInTheDocument();
			expect(screen.getByRole("option", { name: "☆" })).toBeInTheDocument();
			expect(screen.getByRole("option", { name: "✓" })).toBeInTheDocument();
		});
	});

	describe("選択状態", () => {
		it("value prop に「◎」を渡すとそれが選択状態になる", () => {
			// Act
			render(<RaceMarkSelect value="◎" onChange={vi.fn()} />);

			// Assert
			expect(screen.getByRole("combobox")).toHaveTextContent("◎");
		});

		it("value prop が null のとき未選択（―）が選択状態になる", () => {
			// Act
			render(<RaceMarkSelect value={null} onChange={vi.fn()} />);

			// Assert
			expect(screen.getByRole("combobox")).toHaveTextContent("―");
		});
	});

	describe("変更イベント", () => {
		it("選択肢を変更すると onChange が選択された値で呼ばれる", async () => {
			// Arrange
			const onChange = vi.fn();
			const user = userEvent.setup();

			// Act
			render(<RaceMarkSelect value={null} onChange={onChange} />);
			await user.click(screen.getByRole("combobox"));
			await user.click(screen.getByRole("option", { name: "◎" }));

			// Assert
			expect(onChange).toHaveBeenCalledWith("◎");
		});

		it("未選択（―）に戻すと onChange が null で呼ばれる", async () => {
			// Arrange
			const onChange = vi.fn();
			const user = userEvent.setup();

			// Act
			render(<RaceMarkSelect value="◎" onChange={onChange} />);
			await user.click(screen.getByRole("combobox"));
			await user.click(screen.getByRole("option", { name: "―" }));

			// Assert
			expect(onChange).toHaveBeenCalledWith(null);
		});
	});
});
