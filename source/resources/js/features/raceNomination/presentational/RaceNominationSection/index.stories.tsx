import type { Meta, StoryObj } from "@storybook/react-vite";
import RaceNominationAddForm from "../RaceNominationAddForm";
import RaceNominationSection from ".";

const meta: Meta<typeof RaceNominationSection> = {
	title: "features/raceNomination/presentational/RaceNominationSection",
	component: RaceNominationSection,
	args: {
		addForm: (
			<RaceNominationAddForm
				value=""
				candidates={[]}
				submitting={false}
				errorMessage={null}
				onValueChange={() => {}}
				onCandidateSelect={() => {}}
				onSubmit={() => {}}
			/>
		),
		onStatusChange: () => {},
		onNoteClick: () => {},
		onDeleteClick: () => {},
	},
};

export default meta;
type Story = StoryObj<typeof RaceNominationSection>;

export const Empty: Story = {
	name: "登録馬なし",
	args: {
		nominations: [],
	},
};

export const WithNominations: Story = {
	name: "登録馬あり（チェック・消し・未設定・メモあり）",
	args: {
		nominations: [
			{
				id: 1,
				horse_id: 1,
				horse_uid: "horse-1",
				horse_name: "ウワサノホース",
				status: "check",
				note: { id: 1, content: "SNS で特別登録の噂", source: "race" },
			},
			{
				id: 2,
				horse_id: 2,
				horse_uid: "horse-2",
				horse_name: "ケシノホース",
				status: "excluded",
				note: null,
			},
			{
				id: 3,
				horse_id: 3,
				horse_uid: "horse-3",
				horse_name: "ミテイノホース",
				status: null,
				note: null,
			},
		],
	},
};

export const WithOtherMarks: Story = {
	name: "他人の印列あり（印・印メモあり／印メモのみ／未設定）",
	args: {
		markColumns: [
			{ id: 1, type: "own", label: null, display_order: 0 },
			{ id: 2, type: "other", label: "Aさん", display_order: 1 },
			{ id: 3, type: "other", label: "新聞B", display_order: 2 },
			{ id: 4, type: "other", label: "", display_order: 3 },
		],
		marks: [
			{ column_id: 2, race_nomination_id: 1, mark_value: "◎" },
			{ column_id: 2, race_nomination_id: 2, mark_value: "△" },
			{ column_id: 3, race_nomination_id: 1, mark_value: "○" },
			{ column_id: 3, race_nomination_id: 3, mark_value: "☆" },
		],
		markMemos: [
			{
				column_id: 2,
				race_nomination_id: 1,
				content: "登録段階から本命と明言",
			},
			{
				column_id: 3,
				race_nomination_id: 2,
				content: "印は未定だが注目馬として言及",
			},
		],
		nominations: WithNominations.args?.nominations ?? [],
	},
};
