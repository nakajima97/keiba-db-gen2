import { Link } from "@inertiajs/react";
import { Button } from "@/components/shadcn/ui/button";
import { formatDateDisplay } from "@/utils/date";
import type { HorseNotesListProps } from "./types";

const HorseNotesList = ({
	notes,
	onAddClick,
	onEditClick,
	onDeleteClick,
}: HorseNotesListProps) => {
	return (
		<div className="flex flex-col gap-3">
			<div className="flex items-center justify-between">
				<h2 className="text-lg font-semibold">メモ</h2>
				<Button variant="outline" size="sm" onClick={onAddClick}>
					メモを追加
				</Button>
			</div>

			{notes.length === 0 ? (
				<p className="text-sm text-muted-foreground">メモがありません</p>
			) : (
				<ul className="flex flex-col gap-2">
					{notes.map((note) => (
						<li
							key={note.id}
							className="flex flex-col gap-2 rounded-md border bg-card p-4"
						>
							<div className="flex flex-wrap items-center justify-between gap-2">
								<div className="flex items-center gap-2 text-sm">
									{note.race !== null ? (
										<>
											<span className="rounded bg-primary/10 px-2 py-0.5 text-xs text-primary">
												レース紐づき
											</span>
											<Link
												href={`/races/${note.race.uid}/result/edit`}
												className="text-primary hover:underline"
											>
												{note.race.label}
											</Link>
										</>
									) : (
										<span className="rounded bg-muted px-2 py-0.5 text-xs text-muted-foreground">
											レース紐づきなし
										</span>
									)}
								</div>
								<span className="text-xs text-muted-foreground">
									更新: {formatDateDisplay(note.updated_at)}
								</span>
							</div>
							<p className="whitespace-pre-wrap text-sm">{note.content}</p>
							<div className="flex justify-end gap-1">
								<Button
									variant="ghost"
									size="sm"
									onClick={() => onEditClick(note.id)}
								>
									編集
								</Button>
								<Button
									variant="ghost"
									size="sm"
									className="text-destructive hover:text-destructive"
									onClick={() => onDeleteClick(note.id)}
								>
									削除
								</Button>
							</div>
						</li>
					))}
				</ul>
			)}
		</div>
	);
};

export default HorseNotesList;

export type { HorseNoteListItem, HorseNotesListProps } from "./types";
