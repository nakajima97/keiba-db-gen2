import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import type { EntryComment } from "../../types";
import CommentOutcomeList from "./index";

const comment: EntryComment = {
	id: 1,
	speaker_type: "trainer",
	speaker_id: 10,
	speaker_name: "加藤 征弘",
	sentiment: "conditional",
	content: "展開が向けば",
	outcome: "hit",
	created_at: "2026-11-22T10:00:00+09:00",
};

describe("CommentOutcomeList", () => {
	it("コメントごとに発言者種別・向き・本文と当否セレクトを表示する", () => {
		// Act
		render(
			<CommentOutcomeList comments={[comment]} pendingKeys={[]} onOutcomeChange={() => {}} />,
		);

		// Assert
		expect(screen.getByText("調教師")).toBeInTheDocument();
		expect(screen.getByText("条件付き")).toBeInTheDocument();
		expect(screen.getByText("展開が向けば")).toBeInTheDocument();
		expect(
			screen.getByRole("combobox", { name: "加藤 征弘のコメント「展開が向けば」の当否" }),
		).toHaveTextContent("当たり");
	});

	it("保存中のコメントの当否セレクトは無効になる", () => {
		// Act
		render(
			<CommentOutcomeList
				comments={[comment]}
				pendingKeys={["trainer-1"]}
				onOutcomeChange={() => {}}
			/>,
		);

		// Assert
		expect(screen.getByRole("combobox")).toBeDisabled();
	});

	it.each([
		["コメントが空", []],
		["出馬表データなし", null],
	])("%s のときは「-」を表示する", (_label, comments) => {
		// Act
		render(
			<CommentOutcomeList comments={comments} pendingKeys={[]} onOutcomeChange={() => {}} />,
		);

		// Assert
		expect(screen.getByText("-")).toBeInTheDocument();
	});
});
