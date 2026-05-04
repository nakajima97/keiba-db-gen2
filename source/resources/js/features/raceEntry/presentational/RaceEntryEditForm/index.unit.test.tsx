import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";
import RaceEntryEditForm from "./index";
import type {
	RaceEntryEditFormErrors,
	RaceEntryEditFormProps,
	RaceEntryEditFormValues,
} from "./types";

vi.mock("@inertiajs/react", () => ({
	Link: ({
		href,
		children,
	}: {
		href: string;
		children: React.ReactNode;
	}) => <a href={href}>{children}</a>,
}));

const defaultValues: RaceEntryEditFormValues = {
	horse_name: "コントレイル",
	jockey_name: "福永祐一",
	frame_number: 2,
	horse_number: 3,
	weight: "57.0",
	horse_weight: "486",
};

const renderForm = (overrides: Partial<RaceEntryEditFormProps> = {}) => {
	const props: RaceEntryEditFormProps = {
		raceUid: "test-uid-123",
		raceInfo: {
			race_date: "2026-04-26",
			venue_name: "東京",
			race_number: 11,
		},
		values: defaultValues,
		errors: {},
		isSubmitting: false,
		onChange: vi.fn(),
		onSubmit: vi.fn(),
		...overrides,
	};
	return { props, ...render(<RaceEntryEditForm {...props} />) };
};

describe("RaceEntryEditForm", () => {
	it("各フィールドが初期値で表示される", () => {
		// Act
		renderForm();

		// Assert
		expect(screen.getByLabelText("馬名")).toHaveValue("コントレイル");
		expect(screen.getByLabelText("騎手名")).toHaveValue("福永祐一");
		expect(screen.getByLabelText("枠番（1〜8）")).toHaveValue(2);
		expect(screen.getByLabelText("馬番（1〜18）")).toHaveValue(3);
		expect(screen.getByLabelText("負担重量（kg）")).toHaveValue(57.0);
		expect(screen.getByLabelText("馬体重（kg、任意）")).toHaveValue(486);
	});

	it("フィールドを変更すると onChange が field, value の引数で呼ばれる", async () => {
		// Arrange
		const onChange = vi.fn();
		const user = userEvent.setup();
		renderForm({ onChange });

		// Act
		await user.type(screen.getByLabelText("馬名"), "X");

		// Assert
		expect(onChange).toHaveBeenCalledWith("horse_name", expect.any(String));
	});

	it("errors に値があると対応フィールドのエラーメッセージが表示される", () => {
		// Arrange
		const errors: RaceEntryEditFormErrors = {
			horse_name: "馬名を入力してください",
			horse_number: "馬番は1〜18の範囲で入力してください",
		};

		// Act
		renderForm({ errors });

		// Assert
		expect(screen.getByText("馬名を入力してください")).toBeInTheDocument();
		expect(
			screen.getByText("馬番は1〜18の範囲で入力してください"),
		).toBeInTheDocument();
	});

	it("isSubmitting=true のとき全フィールドが disabled、ボタンに「更新中...」が表示される", () => {
		// Act
		renderForm({ isSubmitting: true });

		// Assert
		expect(screen.getByLabelText("馬名")).toBeDisabled();
		expect(screen.getByLabelText("騎手名")).toBeDisabled();
		expect(screen.getByLabelText("枠番（1〜8）")).toBeDisabled();
		expect(screen.getByLabelText("馬番（1〜18）")).toBeDisabled();
		expect(screen.getByLabelText("負担重量（kg）")).toBeDisabled();
		expect(screen.getByLabelText("馬体重（kg、任意）")).toBeDisabled();
		const submitButton = screen.getByRole("button", { name: "更新中..." });
		expect(submitButton).toBeDisabled();
	});

	it("フォームを submit すると onSubmit が呼ばれる", async () => {
		// Arrange
		const onSubmit = vi.fn();
		const user = userEvent.setup();
		renderForm({ onSubmit });

		// Act
		await user.click(screen.getByRole("button", { name: "更新" }));

		// Assert
		expect(onSubmit).toHaveBeenCalledTimes(1);
	});
});
