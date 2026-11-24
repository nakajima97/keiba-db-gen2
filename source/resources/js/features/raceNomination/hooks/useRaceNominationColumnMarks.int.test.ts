import { act, renderHook } from "@testing-library/react";
import { beforeEach, describe, expect, it, vi } from "vitest";

vi.mock("@/features/raceNomination/requests/raceNominationColumnMarks", () => ({
	upsertNominationColumnMark: vi.fn(),
}));
vi.mock("sonner", () => ({
	toast: { error: vi.fn() },
}));

import { toast } from "sonner";
import { upsertNominationColumnMark } from "@/features/raceNomination/requests/raceNominationColumnMarks";
import { useRaceNominationColumnMarks } from "./useRaceNominationColumnMarks";

describe("useRaceNominationColumnMarks", () => {
	beforeEach(() => {
		vi.clearAllMocks();
	});

	it("ハッピーパス: handleMarkChange を呼ぶと API が正しい引数で呼ばれ、marks の該当セルが上書きされること", async () => {
		// Arrange
		vi.mocked(upsertNominationColumnMark).mockResolvedValue(null);
		const { result } = renderHook(() =>
			useRaceNominationColumnMarks("race-uid", [
				{ column_id: 10, race_nomination_id: 20, mark_value: "◎" },
			]),
		);

		// Act
		await act(async () => {
			await result.current.handleMarkChange({
				columnId: 10,
				raceNominationId: 20,
				markValue: "☆",
			});
		});

		// Assert
		expect(upsertNominationColumnMark).toHaveBeenCalledWith(
			"race-uid",
			10,
			20,
			"☆",
		);
		expect(result.current.marks).toEqual([
			{ column_id: 10, race_nomination_id: 20, mark_value: "☆" },
		]);
	});

	it("API が失敗すると該当セルを元の印に戻してエラーを通知すること", async () => {
		// Arrange
		vi.mocked(upsertNominationColumnMark).mockRejectedValue(new Error("failed"));
		const { result } = renderHook(() =>
			useRaceNominationColumnMarks("race-uid", [
				{ column_id: 10, race_nomination_id: 20, mark_value: "◎" },
			]),
		);

		// Act
		await act(async () => {
			await result.current.handleMarkChange({
				columnId: 10,
				raceNominationId: 20,
				markValue: null,
			});
		});

		// Assert
		expect(result.current.marks).toEqual([
			{ column_id: 10, race_nomination_id: 20, mark_value: "◎" },
		]);
		expect(toast.error).toHaveBeenCalledWith("印の更新に失敗しました");
	});
});
