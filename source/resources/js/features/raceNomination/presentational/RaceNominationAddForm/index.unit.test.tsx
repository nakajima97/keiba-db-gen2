import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";
import RaceNominationAddForm from "./index";
import type { RaceNominationAddFormProps } from "./types";

const buildProps = (
	overrides: Partial<RaceNominationAddFormProps> = {},
): RaceNominationAddFormProps => ({
	value: "",
	candidates: [],
	submitting: false,
	errorMessage: null,
	onValueChange: vi.fn(),
	onCandidateSelect: vi.fn(),
	onSubmit: vi.fn(),
	...overrides,
});

describe("RaceNominationAddForm", () => {
	it("馬名が入力された状態で Enter を押すと onSubmit が呼ばれる", async () => {
		// Arrange
		const user = userEvent.setup();
		const props = buildProps({ value: "ウワサノホース" });
		render(<RaceNominationAddForm {...props} />);

		// Act
		await user.type(screen.getByLabelText("登録馬の馬名"), "{Enter}");

		// Assert
		expect(props.onSubmit).toHaveBeenCalledTimes(1);
	});

	it("馬名が空白のみのときは追加ボタンが押せず onSubmit も呼ばれない", async () => {
		// Arrange
		const user = userEvent.setup();
		const props = buildProps({ value: "  " });
		render(<RaceNominationAddForm {...props} />);

		// Act
		await user.type(screen.getByLabelText("登録馬の馬名"), "{Enter}");

		// Assert
		expect(screen.getByRole("button", { name: "追加" })).toBeDisabled();
		expect(props.onSubmit).not.toHaveBeenCalled();
	});

	it("入力すると onValueChange に入力値が渡される", async () => {
		// Arrange
		const user = userEvent.setup();
		const props = buildProps();
		render(<RaceNominationAddForm {...props} />);

		// Act
		await user.type(screen.getByLabelText("登録馬の馬名"), "ア");

		// Assert
		expect(props.onValueChange).toHaveBeenCalledWith("ア");
	});

	it("候補が生年付きで表示され、クリックすると onCandidateSelect に候補が渡される", async () => {
		// Arrange
		const user = userEvent.setup();
		const candidate = { id: 1, name: "ウワサノホース", birth_year: 2022 };
		const props = buildProps({
			value: "ウワサ",
			candidates: [candidate, { id: 2, name: "ウワサノホース2", birth_year: null }],
		});
		render(<RaceNominationAddForm {...props} />);

		// Act
		await user.click(
			screen.getByRole("button", { name: "ウワサノホース（2022年生）" }),
		);

		// Assert
		expect(screen.getByRole("button", { name: "ウワサノホース2" })).toBeInTheDocument();
		expect(props.onCandidateSelect).toHaveBeenCalledWith(candidate);
	});

	it("errorMessage があるときはエラーメッセージが表示される", () => {
		// Act
		render(
			<RaceNominationAddForm
				{...buildProps({ errorMessage: "この馬は既に登録馬に追加されています。" })}
			/>,
		);

		// Assert
		expect(
			screen.getByText("この馬は既に登録馬に追加されています。"),
		).toBeInTheDocument();
	});
});
