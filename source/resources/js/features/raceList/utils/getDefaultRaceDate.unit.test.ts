import { describe, it, expect, vi, afterEach } from "vitest";
import { getDefaultRaceDate } from "./getDefaultRaceDate";

afterEach(() => {
	vi.useRealTimers();
});

const mockDate = (dateStr: string) => {
	vi.useFakeTimers();
	vi.setSystemTime(new Date(dateStr));
};

describe("getDefaultRaceDate", () => {
	it("今日が日曜日のとき今日の日付を返す", () => {
		mockDate("2026-04-19"); // Sunday

		expect(getDefaultRaceDate()).toBe("2026-04-19");
	});

	it("今日が土曜日のとき今日の日付を返す", () => {
		mockDate("2026-04-18"); // Saturday

		expect(getDefaultRaceDate()).toBe("2026-04-18");
	});

	it("今日が月曜日のとき直前の日曜日を返す", () => {
		mockDate("2026-04-20"); // Monday

		expect(getDefaultRaceDate()).toBe("2026-04-19");
	});

	it("今日が火曜日のとき直前の日曜日を返す", () => {
		mockDate("2026-04-21"); // Tuesday

		expect(getDefaultRaceDate()).toBe("2026-04-19");
	});

	it("今日が水曜日のとき直前の日曜日を返す", () => {
		mockDate("2026-04-22"); // Wednesday

		expect(getDefaultRaceDate()).toBe("2026-04-19");
	});

	it("今日が木曜日のとき直前の日曜日を返す", () => {
		mockDate("2026-04-23"); // Thursday

		expect(getDefaultRaceDate()).toBe("2026-04-19");
	});

	it("今日が金曜日のとき直前の日曜日を返す", () => {
		mockDate("2026-04-24"); // Friday

		expect(getDefaultRaceDate()).toBe("2026-04-19");
	});
});
