import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import CommentStatsLabel from "./index";

describe("CommentStatsLabel", () => {
	it("判定済み件数がある向きだけを「当たり/判定済み」で中黒区切りに表示する", () => {
		// Act
		render(
			<CommentStatsLabel
				stats={{
					positive: { hit: 3, judged: 10 },
					conditional: { hit: 0, judged: 0 },
					negative: { hit: 4, judged: 4 },
				}}
			/>,
		);

		// Assert
		expect(screen.getByText("前 3/10・弱 4/4")).toBeInTheDocument();
	});

	it("すべての向きで判定済みが 0 件なら何も表示しない", () => {
		// Act
		const { container } = render(
			<CommentStatsLabel
				stats={{
					positive: { hit: 0, judged: 0 },
					conditional: { hit: 0, judged: 0 },
					negative: { hit: 0, judged: 0 },
				}}
			/>,
		);

		// Assert
		expect(container).toBeEmptyDOMElement();
	});

	it("stats が null なら何も表示しない", () => {
		// Act
		const { container } = render(<CommentStatsLabel stats={null} />);

		// Assert
		expect(container).toBeEmptyDOMElement();
	});
});
