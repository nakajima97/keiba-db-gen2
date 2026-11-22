import { renderHook, waitFor } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";

vi.mock("@/features/raceNomination/requests/raceNominations", () => ({
	searchHorses: vi.fn(),
}));

import { searchHorses } from "@/features/raceNomination/requests/raceNominations";
import { useHorseCandidates } from "./useHorseCandidates";

describe("useHorseCandidates", () => {
	it("ハッピーパス: 入力した馬名で検索した候補が返る", async () => {
		// Arrange
		const candidates = [{ id: 1, name: "ウワサノホース", birth_year: 2022 }];
		vi.mocked(searchHorses).mockResolvedValue(candidates);

		// Act
		const { result } = renderHook(() => useHorseCandidates(" ウワサ "));

		// Assert
		await waitFor(() => expect(result.current).toEqual(candidates));
		expect(searchHorses).toHaveBeenCalledWith("ウワサ", expect.any(AbortSignal));
	});
});
