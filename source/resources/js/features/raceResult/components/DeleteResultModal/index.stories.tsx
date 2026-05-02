import type { Meta, StoryObj } from "@storybook/react-vite";
import DeleteResultModal from ".";

const meta: Meta<typeof DeleteResultModal> = {
	title: "features/raceResult/components/DeleteResultModal",
	component: DeleteResultModal,
	args: {
		open: true,
		onConfirm: () => {},
		onCancel: () => {},
	},
};

export default meta;
type Story = StoryObj<typeof DeleteResultModal>;

export const Default: Story = {
	name: "通常状態",
	args: {
		isLoading: false,
		errorMessage: null,
	},
};

export const Loading: Story = {
	name: "削除実行中（ボタンdisabled・ラベル変化）",
	args: {
		isLoading: true,
		errorMessage: null,
	},
};

export const WithError: Story = {
	name: "削除失敗（エラー表示）",
	args: {
		isLoading: false,
		errorMessage: "レース結果の削除に失敗しました。時間をおいて再度お試しください。",
	},
};

export const Mobile: Story = {
	name: "モバイル表示",
	globals: {
		viewport: { value: "mobile1", isRotated: false },
	},
	args: {
		isLoading: false,
		errorMessage: null,
	},
};
