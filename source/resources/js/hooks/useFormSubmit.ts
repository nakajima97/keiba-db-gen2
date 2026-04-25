import { router } from "@inertiajs/react";
import { useState } from "react";

export type UseFormSubmitOptions = {
	url: string;
	method?: "post" | "put" | "patch" | "delete";
	onSuccess?: () => void;
	onError?: (errors: Record<string, string>) => void;
	onFinish?: () => void;
};

export type UseFormSubmitReturn<TData> = {
	isSubmitting: boolean;
	handleSubmit: (data: TData) => void;
};

/**
 * フォーム送信の共通ロジックを提供するフック。
 *
 * 目的: 複数フォームコンテナで重複していた router 呼び出しと isSubmitting 状態管理を共通化する。
 * 副作用: router の HTTP メソッド呼び出しと isSubmitting state の更新を行う。
 * エラー方針: バリデーションエラー等は onError コールバックに渡し、toast 表示や
 *             フィールド別のエラー振り分けなどコンテナ固有のロジックは呼び出し側で実装する。
 */
export const useFormSubmit = <TData>(
	options: UseFormSubmitOptions,
): UseFormSubmitReturn<TData> => {
	const { url, method = "post", onSuccess, onError, onFinish } = options;
	const [isSubmitting, setIsSubmitting] = useState(false);

	const handleSubmit = (data: TData) => {
		setIsSubmitting(true);

		const visitOptions = {
			onSuccess: () => {
				onSuccess?.();
			},
			onError: (errors: Record<string, string>) => {
				onError?.(errors);
			},
			onFinish: () => {
				setIsSubmitting(false);
				onFinish?.();
			},
		};

		switch (method) {
			case "post":
				router.post(url, data as never, visitOptions);
				break;
			case "put":
				router.put(url, data as never, visitOptions);
				break;
			case "patch":
				router.patch(url, data as never, visitOptions);
				break;
			case "delete":
				router.delete(url, visitOptions);
				break;
		}
	};

	return { isSubmitting, handleSubmit };
};
