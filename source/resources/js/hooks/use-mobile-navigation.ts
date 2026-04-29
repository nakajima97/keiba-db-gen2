import { useCallback } from "react";

export type CleanupFn = () => void;

export const useMobileNavigation = (): CleanupFn => {
	return useCallback(() => {
		// Remove pointer-events style from body...
		document.body.style.removeProperty("pointer-events");
	}, []);
};
