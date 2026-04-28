import { useSyncExternalStore } from "react";

const MOBILE_BREAKPOINT = 768;

const mql =
	typeof window === "undefined"
		? undefined
		: window.matchMedia(`(max-width: ${MOBILE_BREAKPOINT - 1}px)`);

const mediaQueryListener = (callback: (event: MediaQueryListEvent) => void) => {
	if (!mql) {
		return () => {};
	}

	mql.addEventListener("change", callback);

	return () => {
		mql.removeEventListener("change", callback);
	};
};

const isSmallerThanBreakpoint = (): boolean => {
	return mql?.matches ?? false;
};

const getServerSnapshot = (): boolean => {
	return false;
};

export const useIsMobile = (): boolean => {
	return useSyncExternalStore(
		mediaQueryListener,
		isSmallerThanBreakpoint,
		getServerSnapshot,
	);
};
