import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi } from "vitest";
import RaceMarkColumnHeader from "./index";
import type { RaceMarkColumn } from "../types";

const ownColumn: RaceMarkColumn = {
	id: 1,
	type: "own",
	label: null,
	display_order: 0,
};

const otherColumn: RaceMarkColumn = {
	id: 2,
	type: "other",
	label: "友人A",
	display_order: 1,
};

describe("RaceMarkColumnHeader", () => {
	describe("自分の印列（type=own）", () => {
		it("「自分」が表示される", () => {
			// Act
			render(
				<RaceMarkColumnHeader
					column={ownColumn}
					onChangeLabel={vi.fn()}
					onRemove={vi.fn()}
				/>,
			);

			// Assert
			expect(screen.getByText("自分")).toBeInTheDocument();
		});

		it("削除ボタンが表示されない", () => {
			// Act
			render(
				<RaceMarkColumnHeader
					column={ownColumn}
					onChangeLabel={vi.fn()}
					onRemove={vi.fn()}
				/>,
			);

			// Assert
			expect(
				screen.queryByRole("button", { name: "この印列を削除" }),
			).not.toBeInTheDocument();
		});

		it("ラベル入力フィールドが表示されない", () => {
			// Act
			render(
				<RaceMarkColumnHeader
					column={ownColumn}
					onChangeLabel={vi.fn()}
					onRemove={vi.fn()}
				/>,
			);

			// Assert
			expect(
				screen.queryByLabelText("他人の印列のラベル"),
			).not.toBeInTheDocument();
		});
	});

	describe("他人の印列（type=other）", () => {
		it("ラベル入力フィールドが表示される", () => {
			// Act
			render(
				<RaceMarkColumnHeader
					column={otherColumn}
					onChangeLabel={vi.fn()}
					onRemove={vi.fn()}
				/>,
			);

			// Assert
			expect(screen.getByLabelText("他人の印列のラベル")).toBeInTheDocument();
		});

		it("削除ボタンが表示される", () => {
			// Act
			render(
				<RaceMarkColumnHeader
					column={otherColumn}
					onChangeLabel={vi.fn()}
					onRemove={vi.fn()}
				/>,
			);

			// Assert
			expect(
				screen.getByRole("button", { name: "この印列を削除" }),
			).toBeInTheDocument();
		});

		it("ラベルを入力すると onChangeLabel が入力値で呼ばれる", async () => {
			// Arrange
			const onChangeLabel = vi.fn();
			const user = userEvent.setup();

			// Act
			render(
				<RaceMarkColumnHeader
					column={{ ...otherColumn, label: "" }}
					onChangeLabel={onChangeLabel}
					onRemove={vi.fn()}
				/>,
			);
			await user.type(screen.getByLabelText("他人の印列のラベル"), "X");

			// Assert
			expect(onChangeLabel).toHaveBeenCalledWith("X");
		});

		it("削除ボタンを押すと onRemove が呼ばれる", async () => {
			// Arrange
			const onRemove = vi.fn();
			const user = userEvent.setup();

			// Act
			render(
				<RaceMarkColumnHeader
					column={otherColumn}
					onChangeLabel={vi.fn()}
					onRemove={onRemove}
				/>,
			);
			await user.click(
				screen.getByRole("button", { name: "この印列を削除" }),
			);

			// Assert
			expect(onRemove).toHaveBeenCalledTimes(1);
		});
	});
});
