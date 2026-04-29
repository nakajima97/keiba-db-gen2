export type HorseNoteListItem = {
	id: number;
	content: string;
	race: {
		uid: string;
		label: string;
	} | null;
	created_at: string;
	updated_at: string;
};

export type HorseNotesListProps = {
	notes: HorseNoteListItem[];
	onAddClick: () => void;
	onEditClick: (noteId: number) => void;
};
