import type { Meta, StoryObj } from "@storybook/react-vite";
import HorseNotesList from ".";

const meta: Meta<typeof HorseNotesList> = {
	title: "features/horseNote/presentational/HorseNotesList",
	component: HorseNotesList,
	args: {
		onAddClick: () => {},
		onEditClick: () => {},
		onDeleteClick: () => {},
	},
};

export default meta;
type Story = StoryObj<typeof HorseNotesList>;

export const Empty: Story = {
	name: "メモなし（空状態）",
	args: {
		notes: [],
	},
};

export const Mixed: Story = {
	name: "メモあり（レース紐づきあり・なし混在）",
	args: {
		notes: [
			{
				id: 1,
				content:
					"次この条件だったら買いたい。芝1600mの稍重がベスト条件。\n併せ馬の動き◎。",
				race: null,
				created_at: "2026-04-20",
				updated_at: "2026-04-20",
			},
			{
				id: 2,
				content:
					"前走は外枠で出遅れ気味。次は内枠なら本命視。鞍上継続騎乗。",
				race: { uid: "abc001", label: "2026/04/19 東京 11R 皐月賞" },
				created_at: "2026-04-19",
				updated_at: "2026-04-22",
			},
			{
				id: 3,
				content: "併せ馬の相手で走っていたが先着してゴール。調子良さそう。",
				race: { uid: "abc002", label: "2026/03/15 中山 10R 弥生賞" },
				created_at: "2026-03-16",
				updated_at: "2026-03-16",
			},
		],
	},
};

export const RaceLinkedOnly: Story = {
	name: "レース紐づきメモのみ",
	args: {
		notes: [
			{
				id: 1,
				content: "前走は外枠で出遅れ気味。次は内枠なら本命視。",
				race: { uid: "abc001", label: "2026/04/19 東京 11R 皐月賞" },
				created_at: "2026-04-19",
				updated_at: "2026-04-19",
			},
		],
	},
};

export const HorseLinkedOnly: Story = {
	name: "レース紐づきなしメモのみ（次走への備忘録）",
	args: {
		notes: [
			{
				id: 1,
				content: "次この条件だったら買いたい。芝1600mの稍重がベスト条件。",
				race: null,
				created_at: "2026-04-20",
				updated_at: "2026-04-20",
			},
		],
	},
};

export const Mobile: Story = {
	name: "モバイル表示",
	globals: {
		viewport: { value: "mobile1", isRotated: false },
	},
	args: {
		notes: [
			{
				id: 1,
				content:
					"次この条件だったら買いたい。芝1600mの稍重がベスト条件。",
				race: null,
				created_at: "2026-04-20",
				updated_at: "2026-04-20",
			},
			{
				id: 2,
				content: "前走は外枠で出遅れ気味。次は内枠なら本命視。",
				race: { uid: "abc001", label: "2026/04/19 東京 11R 皐月賞" },
				created_at: "2026-04-19",
				updated_at: "2026-04-19",
			},
		],
	},
};
