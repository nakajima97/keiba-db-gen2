import { render, screen } from "@testing-library/react";
import { describe, it, expect, vi } from "vitest";
import RaceResultCreate from "./create";

vi.mock("@inertiajs/react", async () => {
	const actual =
		await vi.importActual<typeof import("@inertiajs/react")>(
			"@inertiajs/react",
		);
	return {
		...actual,
		Head: ({ title }: { title: string }) => <title>{title}</title>,
		usePage: () => ({
			props: {
				race: {
					uid: "test-uid-123",
					venue_name: "東京",
					race_date: "2026-04-05",
					race_number: 1,
				},
			},
		}),
	};
});

describe("RaceResultCreate ページ", () => {
	it("ハッピーパス: ページが正常にレンダリングされ、RaceResultForm が表示される", () => {
		// Act
		render(<RaceResultCreate />);

		// Assert
		expect(screen.getByText("レース結果入力")).toBeInTheDocument();
		expect(screen.getByLabelText("着順情報をペースト")).toBeInTheDocument();
		expect(screen.getByLabelText("払い戻し情報をペースト")).toBeInTheDocument();
		expect(
			screen.getByRole("button", { name: "保存する" }),
		).toBeInTheDocument();
	});
});
