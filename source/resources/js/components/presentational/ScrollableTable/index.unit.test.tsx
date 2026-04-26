import { render, screen } from "@testing-library/react";
import { describe, it, expect } from "vitest";
import ScrollableTable from "./index";

describe("ScrollableTable", () => {
	describe("children のレンダリング", () => {
		it("渡された children が table 内にレンダリングされる", () => {
			// Act
			render(
				<ScrollableTable>
					<thead>
						<tr>
							<th>ヘッダー1</th>
							<th>ヘッダー2</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>セル1</td>
							<td>セル2</td>
						</tr>
					</tbody>
				</ScrollableTable>,
			);

			// Assert
			expect(screen.getByText("ヘッダー1")).toBeInTheDocument();
			expect(screen.getByText("ヘッダー2")).toBeInTheDocument();
			expect(screen.getByText("セル1")).toBeInTheDocument();
			expect(screen.getByText("セル2")).toBeInTheDocument();
		});
	});

	describe("ラッパー div のスタイル", () => {
		it("ラッパー div に overflow-x-auto クラスが適用されている", () => {
			// Act
			render(
				<ScrollableTable>
					<tbody>
						<tr>
							<td>cell</td>
						</tr>
					</tbody>
				</ScrollableTable>,
			);

			// Assert
			const wrapper = screen.getByRole("table").parentElement;
			expect(wrapper).toHaveClass("overflow-x-auto");
		});

		it("ラッパー div に rounded-xl および border クラスが適用されている", () => {
			// Act
			render(
				<ScrollableTable>
					<tbody>
						<tr>
							<td>cell</td>
						</tr>
					</tbody>
				</ScrollableTable>,
			);

			// Assert
			const wrapper = screen.getByRole("table").parentElement;
			expect(wrapper).toHaveClass("rounded-xl");
			expect(wrapper).toHaveClass("border");
		});
	});

	describe("table 要素のスタイル", () => {
		it("table 要素に min-w-max クラスが適用されている", () => {
			// Act
			render(
				<ScrollableTable>
					<tbody>
						<tr>
							<td>cell</td>
						</tr>
					</tbody>
				</ScrollableTable>,
			);

			// Assert
			expect(screen.getByRole("table")).toHaveClass("min-w-max");
		});

		it("table 要素に w-full および text-sm クラスが適用されている", () => {
			// Act
			render(
				<ScrollableTable>
					<tbody>
						<tr>
							<td>cell</td>
						</tr>
					</tbody>
				</ScrollableTable>,
			);

			// Assert
			const table = screen.getByRole("table");
			expect(table).toHaveClass("w-full");
			expect(table).toHaveClass("text-sm");
		});
	});
});
