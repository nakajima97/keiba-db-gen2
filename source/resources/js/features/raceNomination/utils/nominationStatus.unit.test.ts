import { describe, expect, it } from "vitest";
import type { RaceNomination } from "../types/raceNomination";
import { buildStatusByHorseId } from "./nominationStatus";

const buildNomination = (
	overrides: Partial<RaceNomination>,
): RaceNomination => ({
	id: 1,
	horse_id: 100,
	horse_uid: "horse-uid",
	horse_name: "テストホース",
	status: null,
	note: null,
	...overrides,
});

describe("buildStatusByHorseId", () => {
	it("チェック／消しが設定された登録馬だけを horse_id ごとの対応表にする", () => {
		// Arrange
		const nominations = [
			buildNomination({ id: 1, horse_id: 100, status: "check" }),
			buildNomination({ id: 2, horse_id: 200, status: "excluded" }),
			buildNomination({ id: 3, horse_id: 300, status: null }),
		];

		// Act
		const result = buildStatusByHorseId(nominations);

		// Assert
		expect(result).toEqual({ 100: "check", 200: "excluded" });
	});

	it("登録馬が空のときは空の対応表を返す", () => {
		// Act
		const result = buildStatusByHorseId([]);

		// Assert
		expect(result).toEqual({});
	});
});
