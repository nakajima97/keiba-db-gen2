import { useCallback, useEffect, useRef } from "react";

/**
 * 引数を保持しつつ delay 後に実行する key 付き debounce フック。
 * 同じ key で再呼び出すと保留中のタイマーをリセットする。
 * cancel(key) で個別キャンセル、アンマウント時には全タイマーを自動キャンセルする。
 *
 * 失敗時の state ロールバックを行わない用途を想定（入力中の最新値を捨てると UX が悪いため）。
 */
export const useDebouncedCallbackByKey = <TArgs extends unknown[]>(
	fn: (...args: TArgs) => void,
	delayMs: number,
): {
	call: (key: string | number, ...args: TArgs) => void;
	cancel: (key: string | number) => void;
} => {
	const fnRef = useRef(fn);
	fnRef.current = fn;
	const timersRef = useRef(
		new Map<string | number, ReturnType<typeof setTimeout>>(),
	);

	useEffect(() => {
		const timers = timersRef.current;
		return () => {
			for (const timer of timers.values()) {
				clearTimeout(timer);
			}
			timers.clear();
		};
	}, []);

	const call = useCallback(
		(key: string | number, ...args: TArgs) => {
			const existing = timersRef.current.get(key);
			if (existing) clearTimeout(existing);
			const timer = setTimeout(() => {
				timersRef.current.delete(key);
				fnRef.current(...args);
			}, delayMs);
			timersRef.current.set(key, timer);
		},
		[delayMs],
	);

	const cancel = useCallback((key: string | number) => {
		const timer = timersRef.current.get(key);
		if (timer) {
			clearTimeout(timer);
			timersRef.current.delete(key);
		}
	}, []);

	return { call, cancel };
};
