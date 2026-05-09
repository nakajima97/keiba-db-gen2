import type { ReactNode } from "react";
import { vi } from "vitest";

type RouterFns = Record<string, ReturnType<typeof vi.fn>>;

type UsePageFn = () => { props: Record<string, unknown> };

const defaultUsePage: UsePageFn = () => ({ props: {} });

const MockHead = ({ title }: { title: string }) => <title>{title}</title>;

const MockLink = ({
	href,
	children,
}: {
	href: string;
	children: ReactNode;
}) => <a href={href}>{children}</a>;

export function createInertiaReactMock(
	opts: {
		usePage?: UsePageFn;
		router?: RouterFns;
		actual?: object;
	} = {},
) {
	return {
		...(opts.actual ?? {}),
		Head: MockHead,
		Link: MockLink,
		usePage: opts.usePage ?? defaultUsePage,
		...(opts.router ? { router: opts.router } : {}),
	};
}

export function createInertiaCoreMock<T extends object>(
	opts: {
		actual?: T;
		router?: RouterFns;
	} = {},
) {
	return {
		...(opts.actual ?? {}),
		router: opts.router ?? {
			post: vi.fn(),
			put: vi.fn(),
			patch: vi.fn(),
			delete: vi.fn(),
			visit: vi.fn(),
			reload: vi.fn(),
		},
	};
}
