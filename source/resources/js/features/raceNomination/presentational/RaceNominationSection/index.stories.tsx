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
