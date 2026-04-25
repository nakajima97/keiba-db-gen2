import { act, renderHook } from "@testing-library/react";
import { beforeEach, describe, expect, it, vi } from "vitest";

vi.mock("@inertiajs/react", () => ({
	router: {
		post: vi.fn(),
	},
}));

import { router } from "@inertiajs/react";
import { useFormSubmit } from "./use-form-submit";

describe("useFormSubmit", () => {
	beforeEach(() => {
		vi.clearAllMocks();
	});

	it("ハッピーパス: handleSubmit を呼ぶと router.post が正しい url とデータで呼ばれ、onSuccess・onFinish が実行されて isSubmitting が false に戻ること", () => {
		// Arrange
		const onSuccess = vi.fn();
		const onFinish = vi.fn();

		vi.mocked(router.post).mockImplementation((_url, _data, options) => {
			options?.onSuccess?.({} as never);
			options?.onFinish?.({} as never);
		});

		const { result } = renderHook(() =>
			useFormSubmit({ url: "/test-url", onSuccess, onFinish }),
		);

		// Act
		act(() => {
			result.current.handleSubmit({ key: "value" });
		});

		// Assert
		expect(router.post).toHaveBeenCalledWith(
			"/test-url",
			{ key: "value" },
			expect.any(Object),
		);
		expect(onSuccess).toHaveBeenCalledTimes(1);
		expect(onFinish).toHaveBeenCalledTimes(1);
		expect(result.current.isSubmitting).toBe(false);
	});
});
