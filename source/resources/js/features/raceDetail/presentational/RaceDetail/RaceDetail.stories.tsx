import type { Meta, StoryObj } from "@storybook/react-vite";
import { fn } from "storybook/test";
import RaceDetail from ".";
import type { RaceDetailProps } from ".";

const meta: Meta<typeof RaceDetail> = {
	title: "features/raceDetail/presentational/RaceDetail",
	component: RaceDetail,
	args: {
		onMarkChange: fn(),
		onAddOtherColumn: fn(),
		onRemoveOtherColumn: fn(),
		onChangeColumnLabel: fn(),
	},
};

export default meta;
type Story = StoryObj<typeof RaceDetail>;

const baseEntries: RaceDetailProps["race"]["entries"] = [
	{
		id: 1,
		horse_id: 1,
		frame_number: 1,
		horse_number: 1,
		horse_name: "サンプルホース1",
		jockey_name: "騎手 一郎",
		weight: 480,
	},
	{
		id: 2,
		horse_id: 2,
		frame_number: 1,
		horse_number: 2,
		horse_name: "サンプルホース2",
		jockey_name: "騎手 二郎",
		weight: 462,
	},
	{
		id: 3,
		horse_id: 3,
		frame_number: 2,
		horse_number: 3,
		horse_name: "サンプルホース3",
		jockey_name: "騎手 三郎",
		weight: null,
	},
	{
		id: 4,
		horse_id: 4,
		frame_number: 2,
		horse_number: 4,
		horse_name: "サンプルホース4",
		jockey_name: "騎手 四郎",
		weight: 510,
	},
];

const ownColumnOnly: RaceDetailProps["race"]["mark_columns"] = [
	{ id: 100, type: "own", label: null, display_order: 0 },
];

const ownAndTwoOtherColumns: RaceDetailProps["race"]["mark_columns"] = [
	{ id: 100, type: "own", label: null, display_order: 0 },
	{ id: 101, type: "other", label: "予想家Aさん", display_order: 1 },
	{ id: 102, type: "other", label: "父親", display_order: 2 },
];

const emptyMarksRace: RaceDetailProps["race"] = {
	uid: "abc001",
	race_date: "2026-04-19",
	venue_name: "東京",
	race_number: 1,
	race_name: "皐月賞",
	entries: baseEntries,
	mark_columns: ownColumnOnly,
	marks: [],
};

export const NoMarks: Story = {
	name: "印が一切設定されていない（自分の印列のみ）",
	args: {
		race: emptyMarksRace,
	},
};

export const RaceNameEmpty: Story = {
	name: "レース名が未登録（race_name が null）",
	args: {
		race: {
			...emptyMarksRace,
			race_name: null,
		},
	},
};

export const OwnMarksPartiallySet: Story = {
	name: "自分の印が一部設定済み",
	args: {
		race: {
			...emptyMarksRace,
			marks: [
				{ column_id: 100, race_entry_id: 1, mark_value: "◎" },
				{ column_id: 100, race_entry_id: 3, mark_value: "○" },
			],
		},
	},
};

export const MultipleOtherColumnsMixed: Story = {
	name: "他人の印列が複数追加され、印が混在",
	args: {
		race: {
			...emptyMarksRace,
			mark_columns: ownAndTwoOtherColumns,
			marks: [
				{ column_id: 100, race_entry_id: 1, mark_value: "◎" },
				{ column_id: 100, race_entry_id: 2, mark_value: "▲" },
				{ column_id: 101, race_entry_id: 1, mark_value: "○" },
				{ column_id: 101, race_entry_id: 4, mark_value: "△" },
				{ column_id: 102, race_entry_id: 3, mark_value: "×" },
			],
		},
	},
};

export const AllMarksSet: Story = {
	name: "すべての印が設定された状態",
	args: {
		race: {
			...emptyMarksRace,
			mark_columns: ownAndTwoOtherColumns,
			marks: [
				{ column_id: 100, race_entry_id: 1, mark_value: "◎" },
				{ column_id: 100, race_entry_id: 2, mark_value: "○" },
				{ column_id: 100, race_entry_id: 3, mark_value: "▲" },
				{ column_id: 100, race_entry_id: 4, mark_value: "△" },
				{ column_id: 101, race_entry_id: 1, mark_value: "○" },
				{ column_id: 101, race_entry_id: 2, mark_value: "◎" },
				{ column_id: 101, race_entry_id: 3, mark_value: "×" },
				{ column_id: 101, race_entry_id: 4, mark_value: "✓" },
				{ column_id: 102, race_entry_id: 1, mark_value: "▲" },
				{ column_id: 102, race_entry_id: 2, mark_value: "△" },
				{ column_id: 102, race_entry_id: 3, mark_value: "✓" },
				{ column_id: 102, race_entry_id: 4, mark_value: "◎" },
			],
		},
	},
};

export const OtherColumnLabelEmpty: Story = {
	name: "他人の印列を追加直後（ラベル未入力）",
	args: {
		race: {
			...emptyMarksRace,
			mark_columns: [
				{ id: 100, type: "own", label: null, display_order: 0 },
				{ id: 101, type: "other", label: "", display_order: 1 },
			],
			marks: [],
		},
	},
};

export const MobileView: Story = {
	name: "モバイル表示",
	globals: {
		viewport: { value: "mobile1", isRotated: false },
	},
	args: {
		race: {
			...emptyMarksRace,
			mark_columns: ownAndTwoOtherColumns,
			marks: [
				{ column_id: 100, race_entry_id: 1, mark_value: "◎" },
				{ column_id: 101, race_entry_id: 2, mark_value: "○" },
			],
		},
	},
};
