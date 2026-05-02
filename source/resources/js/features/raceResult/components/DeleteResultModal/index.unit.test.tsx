import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";
import DeleteResultModal from "./index";

const noop = () => {};

describe("DeleteResultModal", () => {
	describe("通常状態（isLoading=false, errorMessage=null）", () => {
		it("タイトルと説明が表示される", () => {
			// Act
			render(
				<DeleteResultModal
					open={true}
					isLoading={false}
					errorMessage={null}
					onConfirm={noop}
					onCancel={noop}
				/>,
			);

			// Assert
			expect(screen.getByText("レース結果を削除")).toBeInTheDocument();
			expect(
				screen.getByText(
					"このレースの着順・払戻データをすべて削除します。この操作は取り消せません。",
				),
			).toBeInTheDocument();
		});

		it("「削除する」ボタンと「キャンセル」ボタンが表示される", () => {
			// Act
			render(
				<DeleteResultModal
					open={true}
					isLoading={false}
					errorMessage={null}
					onConfirm={noop}
					onCancel={noop}
				/>,
			);

			// Assert
			expect(
				screen.getByRole("button", { name: "削除する" }),
			).toBeInTheDocument();
			expect(
				screen.getByRole("button", { name: "キャンセル" }),
			).toBeInTheDocument();
		});

		it("エラーメッセージが表示されない", () => {
			// Act
			render(
				<DeleteResultModal
					open={true}
					isLoading={false}
					errorMessage={null}
					onConfirm={noop}
					onCancel={noop}
				/>,
			);

			// Assert
			expect(screen.queryByText("エラー")).not.toBeInTheDocument();
		});
	});

	describe("削除実行中（isLoading=true）", () => {
		it("「削除中...」が表示される", () => {
			// Act
			render(
				<DeleteResultModal
					open={true}
					isLoading={true}
					errorMessage={null}
					onConfirm={noop}
					onCancel={noop}
				/>,
			);

			// Assert
			expect(
				screen.getByRole("button", { name: "削除中..." }),
			).toBeInTheDocument();
		});

		it("削除ボタンが disabled になる", () => {
			// Act
			render(
				<DeleteResultModal
					open={true}
					isLoading={true}
					errorMessage={null}
					onConfirm={noop}
					onCancel={noop}
				/>,
			);

			// Assert
			expect(screen.getByRole("button", { name: "削除中..." })).toBeDisabled();
		});

		it("キャンセルボタンが disabled になる", () => {
			// Act
			render(
				<DeleteResultModal
					open={true}
					isLoading={true}
					errorMessage={null}
					onConfirm={noop}
					onCancel={noop}
				/>,
			);

			// Assert
			expect(screen.getByRole("button", { name: "キャンセル" })).toBeDisabled();
		});
	});

	describe("エラー表示（errorMessage が設定されている）", () => {
		it("エラーメッセージが表示される", () => {
			// Act
			render(
				<DeleteResultModal
					open={true}
					isLoading={false}
					errorMessage="削除に失敗しました"
					onConfirm={noop}
					onCancel={noop}
				/>,
			);

			// Assert
			expect(screen.getByText("削除に失敗しました")).toBeInTheDocument();
		});
	});

	describe("非表示（open=false）", () => {
		it("タイトルが表示されない", () => {
			// Act
			render(
				<DeleteResultModal
					open={false}
					isLoading={false}
					errorMessage={null}
					onConfirm={noop}
					onCancel={noop}
				/>,
			);

			// Assert
			expect(screen.queryByText("レース結果を削除")).not.toBeInTheDocument();
		});
	});

	describe("コールバック", () => {
		it("「削除する」ボタンをクリックすると onConfirm が呼ばれる", async () => {
			// Arrange
			const onConfirm = vi.fn();
			const user = userEvent.setup();

			// Act
			render(
				<DeleteResultModal
					open={true}
					isLoading={false}
					errorMessage={null}
					onConfirm={onConfirm}
					onCancel={noop}
				/>,
			);
			await user.click(screen.getByRole("button", { name: "削除する" }));

			// Assert
			expect(onConfirm).toHaveBeenCalledTimes(1);
		});

		it("「キャンセル」ボタンをクリックすると onCancel が呼ばれる", async () => {
			// Arrange
			const onCancel = vi.fn();
			const user = userEvent.setup();

			// Act
			render(
				<DeleteResultModal
					open={true}
					isLoading={false}
					errorMessage={null}
					onConfirm={noop}
					onCancel={onCancel}
				/>,
			);
			await user.click(screen.getByRole("button", { name: "キャンセル" }));

			// Assert
			expect(onCancel).toHaveBeenCalled();
		});
	});
});
