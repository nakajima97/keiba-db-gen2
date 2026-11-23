import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { afterEach, describe, expect, it, vi } from "vitest";
import EntryCommentModalContainer from "./index";

const buildResponse = (status: number, body: unknown) =>
	new Response(JSON.stringify(body), {
		status,
		headers: { "Content-Type": "application/json" },
	});

const emptyStats = {
	positive: { hit: 0, judged: 0 },
	conditional: { hit: 0, judged: 0 },
	negative: { hit: 0, judged: 0 },
};

describe("EntryCommentModalContainer", () => {
	afterEach(() => {
		vi.restoreAllMocks();
	});

	it("ハッピーパス: 騎手・条件付きを選んでコメントを追加すると POST API が呼ばれ onCommentsChange に一覧と的中率が渡る", async () => {
		// Arrange
		const created = {
			id: 5,
			speaker_type: "jockey",
			speaker_id: 20,
			speaker_name: "ルメール",
			sentiment: "conditional",
			content: "展開が向けば",
			outcome: null,
			created_at: "2026-11-22T10:00:00+09:00",
		};
		const stats = { trainer_id: 10, trainer: emptyStats, jockey_id: 20, jockey: emptyStats };
		vi.spyOn(global, "fetch").mockResolvedValue(
			buildResponse(201, { data: created, stats }),
		);
		const user = userEvent.setup();
		const onCommentsChange = vi.fn();

		// Act
		render(
			<EntryCommentModalContainer
				open={true}
				raceUid="race-uid"
				raceEntryId={3}
				horseName="ディープスター"
				trainerName="加藤 征弘"
				jockeyName="ルメール"
				comments={[]}
				onClose={() => {}}
				onCommentsChange={onCommentsChange}
			/>,
		);
		await user.click(screen.getByRole("radio", { name: "騎手（ルメール）" }));
		await user.click(screen.getByRole("radio", { name: "条件付き" }));
		await user.type(screen.getByLabelText("コメント内容"), "展開が向けば");
		await user.click(screen.getByRole("button", { name: "追加" }));

		// Assert
		await waitFor(() => {
			expect(onCommentsChange).toHaveBeenCalledTimes(1);
		});
		const [change] = onCommentsChange.mock.calls[0];
		expect(change.raceEntryId).toBe(3);
		expect(change.stats).toEqual(stats);
		expect(change.updateComments([])).toEqual([created]);
		const [url, init] = (global.fetch as ReturnType<typeof vi.fn>).mock
			.calls[0] as [string, RequestInit];
		expect(url).toBe("/api/races/race-uid/entries/3/comments");
		expect(init.method).toBe("POST");
		expect(JSON.parse(init.body as string)).toEqual({
			speaker_type: "jockey",
			sentiment: "conditional",
			content: "展開が向けば",
			outcome: null,
		});
		expect(screen.getByLabelText("コメント内容")).toHaveValue("");
	});
});
