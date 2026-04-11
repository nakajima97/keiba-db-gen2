import { render, screen } from "@testing-library/react";
import { describe, it, expect, vi } from "vitest";
import Dashboard from "./dashboard";

vi.mock("@inertiajs/react", () => ({
	Head: ({ title }: { title: string }) => <title>{title}</title>,
}));

vi.mock("@/routes", () => ({
	dashboard: () => "/dashboard",
}));

vi.mock("@/components/shadcn/ui/select", () => ({
	Select: ({ children }: { children: React.ReactNode }) => (
		<div>{children}</div>
	),
	SelectTrigger: ({ children }: { children: React.ReactNode }) => (
		<div>{children}</div>
	),
	SelectValue: () => <span />,
	SelectContent: ({ children }: { children: React.ReactNode }) => (
		<div>{children}</div>
	),
	SelectItem: ({
		value,
		children,
	}: {
		value: string;
		children: React.ReactNode;
	}) => <div data-value={value}>{children}</div>,
}));

describe("Dashboard ページ", () => {
	it("ハッピーパス: ダッシュボードページがレンダリングされダミーデータが表示される", () => {
		// Act
		render(<Dashboard />);

		// Assert
		expect(document.title).toBe("収支ダッシュボード");
		expect(screen.getByText("収支ダッシュボード")).toBeInTheDocument();
		expect(screen.getByText("2026/04/05")).toBeInTheDocument();
	});
});
