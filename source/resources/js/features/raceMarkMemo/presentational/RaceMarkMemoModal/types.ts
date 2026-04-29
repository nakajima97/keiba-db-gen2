import type { MarkValue } from "@/features/raceDetail/presentational/RaceDetail/types";

export type RaceMarkMemoModalMode = "create" | "edit";

export type RaceMarkMemoModalProps = {
	open: boolean;
	mode: RaceMarkMemoModalMode;
	horseName: string;
	columnLabel: string;
	markValue: MarkValue | null;
	content: string;
	contentMaxLength: number;
	errorMessage: string | null;
	submitting: boolean;
	onContentChange: (value: string) => void;
	onOpenChange: (open: boolean) => void;
	onSubmit: () => void;
	onDelete?: () => void;
};
