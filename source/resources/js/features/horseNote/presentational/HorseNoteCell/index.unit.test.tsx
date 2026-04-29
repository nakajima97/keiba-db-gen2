import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi } from "vitest";
import HorseNoteCell from "./index";

describe("HorseNoteCell", () => {
	describe("メモ内容の表示", () => {
		it("content がある場合、メモ内容が表示される", () => {
			// Act
			render(
				<HorseNoteCell
					content="前走は外枠で出遅れ気味。"
					source="race"
					onClick={() => {}}
				/>,
			);

			// Assert
			expect(
				screen.getByText("前走は外枠で出遅れ気味。"),
			).toBeInTheDocument();
		});

		it("content が null の場合、メモ追加用 UI が表示される", () => {
			// Act
			render(
				<HorseNoteCell content={null} source={null} onClick={() => {}} />,
			);

			// Assert
			expect(
				screen.getByRole("button", { name: "+ メモを追加" }),
			).toBeInTheDocument();
		});
	});

	describe("source による表示の差別化", () => {
		it('source="horse" の場合、レース紐づきなし旨の補足が表示される', () => {
			// Act
			render(
				<HorseNoteCell
					content="次走への備忘録"
					source="horse"
					onClick={() => {}}
				/>,
			);

			// Assert
			expect(
				screen.getByText("（レース紐づきなしのメモ）"),
			).toBeInTheDocument();
		});

		it('source="race" の場合、レース紐づきなし旨の補足は表示されない', () => {
			// Act
			render(
				<HorseNoteCell
					content="このレースに関するメモ"
					source="race"
					onClick={() => {}}
				/>,
			);

			// Assert
			expect(
				screen.queryByText("（レース紐づきなしのメモ）"),
			).not.toBeInTheDocument();
		});
	});

	describe("クリック動作", () => {
		it("メモ内容クリックで onClick が呼ばれる", async () => {
			// Arrange
			const onClick = vi.fn();
			const user = userEvent.setup();

			// Act
			render(
				<HorseNoteCell
					content="メモ"
					source="race"
					onClick={onClick}
				/>,
			);
			await user.click(screen.getByRole("button"));

			// Assert
			expect(onClick).toHaveBeenCalledTimes(1);
		});

		it("メモ追加ボタンクリックで onClick が呼ばれる", async () => {
			// Arrange
			const onClick = vi.fn();
			const user = userEvent.setup();

			// Act
			render(
				<HorseNoteCell content={null} source={null} onClick={onClick} />,
			);
			await user.click(screen.getByRole("button", { name: "+ メモを追加" }));

			// Assert
			expect(onClick).toHaveBeenCalledTimes(1);
		});
	});
});
