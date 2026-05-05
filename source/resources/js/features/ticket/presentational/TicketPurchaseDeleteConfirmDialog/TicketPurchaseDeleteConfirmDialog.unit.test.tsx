import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";
import TicketPurchaseDeleteConfirmDialog from "./index";
import type { TicketPurchaseDeleteConfirmDialogProps } from "./types";

const noop = () => {};

const baseTicketSummary = {
	raceDate: "2026-04-05",
	venueName: "東京",
	raceNumber: 1,
	ticketTypeLabel: "馬連",
};

const baseProps: TicketPurchaseDeleteConfirmDialogProps = {
	open: true,
	ticketSummary: baseTicketSummary,
	submitting: false,
	errorMessage: null,
	onOpenChange: noop,
	onConfirm: noop,
};

describe("TicketPurchaseDeleteConfirmDialog", () => {
	it("open=true のとき確認モーダルが表示される", () => {
		// Act
		render(<TicketPurchaseDeleteConfirmDialog {...baseProps} />);

		// Assert
		expect(
			screen.getByRole("button", { name: "削除する" }),
		).toBeInTheDocument();
	});

	it("モーダルに対象馬券のサマリー（日付・レース場・レース番号・券種）が表示される", () => {
		// Arrange
		const props: TicketPurchaseDeleteConfirmDialogProps = {
			...baseProps,
			ticketSummary: {
				raceDate: "2026-04-05",
				venueName: "東京",
				raceNumber: 1,
				ticketTypeLabel: "馬連",
			},
		};

		// Act
		render(<TicketPurchaseDeleteConfirmDialog {...props} />);

		// Assert
		expect(screen.getByText(/2026\/04\/05/)).toBeInTheDocument();
		expect(screen.getByText(/東京/)).toBeInTheDocument();
		expect(screen.getByText(/1R/)).toBeInTheDocument();
		expect(screen.getByText(/馬連/)).toBeInTheDocument();
	});

	it("「削除する」「キャンセル」ボタンが表示される", () => {
		// Act
		render(<TicketPurchaseDeleteConfirmDialog {...baseProps} />);

		// Assert
		expect(
			screen.getByRole("button", { name: "削除する" }),
		).toBeInTheDocument();
		expect(
			screen.getByRole("button", { name: "キャンセル" }),
		).toBeInTheDocument();
	});

	it("「削除する」クリックで onConfirm が呼ばれる", async () => {
		// Arrange
		const onConfirm = vi.fn();
		const user = userEvent.setup();

		// Act
		render(
			<TicketPurchaseDeleteConfirmDialog
				{...baseProps}
				onConfirm={onConfirm}
			/>,
		);
		await user.click(screen.getByRole("button", { name: "削除する" }));

		// Assert
		expect(onConfirm).toHaveBeenCalledTimes(1);
	});

	it("「キャンセル」クリックで onOpenChange が false で呼ばれる", async () => {
		// Arrange
		const onOpenChange = vi.fn();
		const user = userEvent.setup();

		// Act
		render(
			<TicketPurchaseDeleteConfirmDialog
				{...baseProps}
				onOpenChange={onOpenChange}
			/>,
		);
		await user.click(screen.getByRole("button", { name: "キャンセル" }));

		// Assert
		expect(onOpenChange).toHaveBeenCalledWith(false);
	});

	it("submitting=true のとき「削除する」ボタンが disabled", () => {
		// Act
		render(
			<TicketPurchaseDeleteConfirmDialog {...baseProps} submitting={true} />,
		);

		// Assert
		expect(screen.getByRole("button", { name: "削除する" })).toBeDisabled();
	});

	it("errorMessage が渡されるとエラーメッセージが表示される", () => {
		// Arrange
		const props: TicketPurchaseDeleteConfirmDialogProps = {
			...baseProps,
			errorMessage: "削除に失敗しました",
		};

		// Act
		render(<TicketPurchaseDeleteConfirmDialog {...props} />);

		// Assert
		expect(screen.getByText("削除に失敗しました")).toBeInTheDocument();
	});

	it("open=false のとき確認モーダルが表示されない", () => {
		// Act
		render(<TicketPurchaseDeleteConfirmDialog {...baseProps} open={false} />);

		// Assert
		expect(
			screen.queryByRole("button", { name: "削除する" }),
		).not.toBeInTheDocument();
	});
});
