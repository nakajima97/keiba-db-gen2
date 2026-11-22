import { act, renderHook } from "@testing-library/react";
import { beforeEach, describe, expect, it, vi } from "vitest";

vi.mock("@/features/raceNomination/requests/raceNominations", async (importOriginal) => ({
	...(await importOriginal<
		typeof import("@/features/raceNomination/requests/raceNominations")
	>()),
	createNomination: vi.fn(),
	upsertNominationMark: vi.fn(),
}));
vi.mock("sonner", () => ({
	toast: { error: vi.fn() },
}));

import {
	createNomination,
	upsertNominationMark,
} from "@/features/raceNomination/requests/raceNominations";
import { useRaceNominations } from "./useRaceNominations";

describe("useRaceNominations", () => {
	beforeEach(() => {
		vi.clearAllMocks();
	});

	it("ハッピーパス: 馬名で追加した登録馬にチェックを付けると nominations と statusByHorseId に反映される", async () => {
		// Arrange
		vi.mocked(createNomination).mockResolvedValue({
			id: 10,
			horse_id: 42,
			horse_uid: "horse-uid-42",
			horse_name: "ウワサノホース",
			status: null,
			note: null,
		});
		vi.mocked(upsertNominationMark).mockResolvedValue();
		const { result } = renderHook(() => useRaceNominations("race-uid", []));

		// Act
		await act(async () => {
			await result.current.addNomination({ horseName: "ウワサノホース" });
		});
		await act(async () => {
			await result.current.changeStatus(10, "check");
		});

		// Assert
		expect(createNomination).toHaveBeenCalledWith("race-uid", {
			horseName: "ウワサノホース",
		});
		expect(upsertNominationMark).toHaveBeenCalledWith("race-uid", 10, "check");
		expect(result.current.nominations).toHaveLength(1);
		expect(result.current.nominations[0].status).toBe("check");
		expect(result.current.statusByHorseId).toEqual({ 42: "check" });
	});
});
