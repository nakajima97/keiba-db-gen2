import { act, renderHook } from "@testing-library/react";
import { beforeEach, describe, expect, it, vi } from "vitest";

vi.mock("../requests/raceNominationMarkColumns", () => ({
	createNominationMarkColumn: vi.fn(),
	updateNominationMarkColumnLabel: vi.fn(),
}));
vi.mock("sonner", () => ({
	toast: { error: vi.fn() },
}));

import { createNominationMarkColumn } from "../requests/raceNominationMarkColumns";
import type { RaceNominationMarkColumn } from "../types/raceNomination";
import { useRaceNominationMarkColumns } from "./useRaceNominationMarkColumns";

describe("useRaceNominationMarkColumns", () => {
	beforeEach(() => {
		vi.clearAllMocks();
	});

	it("handleAddColumn を呼ぶと登録馬用の印列追加 API が叩かれ、楽観的に追加された一時行がレスポンスで置き換わる", async () => {
		// Arrange
		const initial: RaceNominationMarkColumn[] = [
			{ id: 1, label: "Aさん", display_order: 1 },
		];
		const created: RaceNominationMarkColumn = {
			id: 99,
			label: "",
			display_order: 2,
		};
		vi.mocked(createNominationMarkColumn).mockResolvedValue(created);
		const { result } = renderHook(() =>
			useRaceNominationMarkColumns("race-uid", initial),
		);

		// Act
		await act(async () => {
			await result.current.handleAddColumn();
		});

		// Assert
		expect(createNominationMarkColumn).toHaveBeenCalledWith("race-uid", "");
		expect(result.current.markColumns).toEqual([initial[0], created]);
	});
});
