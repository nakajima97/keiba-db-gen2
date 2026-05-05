import { act, renderHook } from "@testing-library/react";
import { beforeEach, describe, expect, it, vi } from "vitest";

vi.mock("@/features/raceDetail/requests/raceMarkColumns", () => ({
	createOtherColumn: vi.fn(),
	updateColumnLabel: vi.fn(),
}));
vi.mock("sonner", () => ({
	toast: { error: vi.fn() },
}));

import { createOtherColumn } from "@/features/raceDetail/requests/raceMarkColumns";
import type { RaceMarkColumn } from "@/features/raceDetail/presentational/RaceDetail/types";
import { useRaceMarkColumns } from "./useRaceMarkColumns";

describe("useRaceMarkColumns", () => {
	beforeEach(() => {
		vi.clearAllMocks();
	});

	it("ハッピーパス: handleAddOtherColumn を呼ぶと createOtherColumn API が叩かれ、楽観的に追加された一時行がレスポンスで置き換わること", async () => {
		// Arrange
		const initial: RaceMarkColumn[] = [
			{ id: 1, type: "own", label: null, display_order: 0 },
		];
		const created: RaceMarkColumn = {
			id: 99,
			type: "other",
			label: "",
			display_order: 1,
		};
		vi.mocked(createOtherColumn).mockResolvedValue(created);
		const { result } = renderHook(() =>
			useRaceMarkColumns("race-uid", initial),
		);

		// Act
		await act(async () => {
			await result.current.handleAddOtherColumn();
		});

		// Assert
		expect(createOtherColumn).toHaveBeenCalledWith("race-uid", "");
		expect(result.current.markColumns).toEqual([initial[0], created]);
	});
});
