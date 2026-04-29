import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi } from "vitest";
import HorseNoteIconButton from "./index";

describe("HorseNoteIconButton", () => {
	describe("アイコンの切り替え", () => {
		it("hasNote=true の場合、ボタンが表示される（aria-label で判別）", () => {
			// Act
			render(
				<HorseNoteIconButton
					hasNote={true}
					ariaLabel="ディープスターのメモ"
					onClick={() => {}}
				/>,
			);

			// Assert
			expect(
				screen.getByRole("button", { name: "ディープスターのメモ" }),
			).toBeInTheDocument();
		});

		it("hasNote=false の場合、ボタンが表示される（aria-label で判別）", () => {
			// Act
			render(
				<HorseNoteIconButton
					hasNote={false}
					ariaLabel="ディープスターのメモを追加"
					onClick={() => {}}
				/>,
			);

			// Assert
			expect(
				screen.getByRole("button", { name: "ディープスターのメモを追加" }),
			).toBeInTheDocument();
		});
	});

	describe("aria-label", () => {
		it("ariaLabel が aria-label 属性に反映される", () => {
			// Act
			render(
				<HorseNoteIconButton
					hasNote={true}
					ariaLabel="任意のラベル"
					onClick={() => {}}
				/>,
			);

			// Assert
			expect(screen.getByRole("button")).toHaveAttribute(
				"aria-label",
				"任意のラベル",
			);
		});
	});

	describe("クリック動作", () => {
		it("ボタンクリックで onClick が呼ばれる", async () => {
			// Arrange
			const onClick = vi.fn();
			const user = userEvent.setup();

			// Act
			render(
				<HorseNoteIconButton
					hasNote={true}
					ariaLabel="ボタン"
					onClick={onClick}
				/>,
			);
			await user.click(screen.getByRole("button"));

			// Assert
			expect(onClick).toHaveBeenCalledTimes(1);
		});
	});
});
