import { Pencil, Trash2 } from "lucide-react";
import AlertError from "@/components/presentational/AlertError";
import { Badge } from "@/components/shadcn/ui/badge";
import { Button } from "@/components/shadcn/ui/button";
import {
	Dialog,
	DialogContent,
	DialogDescription,
	DialogHeader,
	DialogTitle,
} from "@/components/shadcn/ui/dialog";
import { ToggleGroup, ToggleGroupItem } from "@/components/shadcn/ui/toggle-group";
import CommentOutcomeSelect from "../CommentOutcomeSelect";
import { commentKey } from "../../utils/commentKey";
import type { CommentSentiment, CommentSpeakerType } from "../../types";
import {
	SENTIMENT_LABELS,
	SENTIMENTS,
	SPEAKER_TYPE_LABELS,
} from "../../utils/labels";
import type { EntryCommentModalProps } from "./types";

/**
 * 出走馬ごとの調教師・騎手コメントの一覧・追加・編集・削除モーダル。
 * 一覧では当否をその場で変更できる。
 */
const EntryCommentModal = ({
	open,
	horseName,
	trainerName,
	jockeyName,
	comments,
	editingKey,
	form,
	contentMaxLength,
	errorMessage,
	submitting,
	pendingKeys,
	onOpenChange,
	onFormChange,
	onSubmit,
	onEditClick,
	onCancelEdit,
	onDeleteClick,
	onOutcomeChange,
}: EntryCommentModalProps) => {
	const isEditing = editingKey !== null;
	const isOverLimit = form.content.length > contentMaxLength;
	const isEmpty = form.content.trim() === "";

	return (
		<Dialog open={open} onOpenChange={onOpenChange}>
			<DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
				<DialogHeader>
					<DialogTitle>調教師・騎手コメント</DialogTitle>
					<DialogDescription>{horseName}</DialogDescription>
				</DialogHeader>

				<section className="flex flex-col gap-2">
					<h3 className="text-sm font-medium">記録済みのコメント</h3>
					{comments.length === 0 ? (
						<p className="text-sm text-muted-foreground">
							コメントはまだありません
						</p>
					) : (
						<ul className="flex flex-col divide-y rounded-md border">
							{comments.map((comment) => {
								const key = commentKey(comment);
								const pending = pendingKeys.includes(key);
								return (
									<li
										key={key}
										className={`flex flex-col gap-2 p-3 ${editingKey === key ? "bg-muted/50" : ""}`}
									>
										<div className="flex flex-wrap items-center gap-2">
											<Badge variant="outline">
												{SPEAKER_TYPE_LABELS[comment.speaker_type]}
											</Badge>
											<span className="text-sm">{comment.speaker_name}</span>
											<Badge variant="secondary">
												{SENTIMENT_LABELS[comment.sentiment]}
											</Badge>
											<div className="ml-auto flex items-center gap-1">
												<CommentOutcomeSelect
													value={comment.outcome}
													ariaLabel={`${comment.speaker_name}のコメントの当否`}
													disabled={pending || submitting}
													onChange={(outcome) =>
														onOutcomeChange(comment, outcome)
													}
												/>
												<Button
													variant="ghost"
													size="icon"
													aria-label="コメントを編集"
													disabled={pending || submitting}
													onClick={() => onEditClick(comment)}
												>
													<Pencil className="h-4 w-4" />
												</Button>
												<Button
													variant="ghost"
													size="icon"
													aria-label="コメントを削除"
													disabled={pending || submitting}
													onClick={() => onDeleteClick(comment)}
												>
													<Trash2 className="h-4 w-4 text-destructive" />
												</Button>
											</div>
										</div>
										<p className="whitespace-pre-wrap break-words text-sm">
											{comment.content}
										</p>
									</li>
								);
							})}
						</ul>
					)}
				</section>

				<section className="flex flex-col gap-4 rounded-md border p-3">
					<h3 className="text-sm font-medium">
						{isEditing ? "コメントを編集" : "コメントを追加"}
					</h3>

					<div className="flex flex-col gap-1">
						<span className="text-sm font-medium">発言者</span>
						<ToggleGroup
							type="single"
							variant="outline"
							value={form.speakerType}
							onValueChange={(value) => {
								if (value !== "") {
									onFormChange({ speakerType: value as CommentSpeakerType });
								}
							}}
							disabled={submitting}
							aria-label="発言者"
							className="justify-start"
						>
							<ToggleGroupItem
								value="trainer"
								disabled={trainerName === null}
								className="px-3"
							>
								調教師{trainerName !== null ? `（${trainerName}）` : "（未登録）"}
							</ToggleGroupItem>
							<ToggleGroupItem value="jockey" className="px-3">
								騎手（{jockeyName}）
							</ToggleGroupItem>
						</ToggleGroup>
					</div>

					<div className="flex flex-col gap-1">
						<span className="text-sm font-medium">向き</span>
						<ToggleGroup
							type="single"
							variant="outline"
							value={form.sentiment}
							onValueChange={(value) => {
								if (value !== "") {
									onFormChange({ sentiment: value as CommentSentiment });
								}
							}}
							disabled={submitting}
							aria-label="向き"
							className="justify-start"
						>
							{SENTIMENTS.map((sentiment) => (
								<ToggleGroupItem key={sentiment} value={sentiment} className="px-3">
									{SENTIMENT_LABELS[sentiment]}
								</ToggleGroupItem>
							))}
						</ToggleGroup>
						<p className="text-xs text-muted-foreground">
							条件付き: 「展開が向けば」「地力を出せれば」のような逃げ道のあるコメント
						</p>
					</div>

					<div className="flex flex-col gap-1">
						<label htmlFor="entry-comment-content" className="text-sm font-medium">
							コメント内容
						</label>
						<textarea
							id="entry-comment-content"
							className="min-h-[100px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:text-muted-foreground"
							placeholder="例: 1週前の追い切りで好時計。状態は過去最高"
							value={form.content}
							onChange={(e) => onFormChange({ content: e.target.value })}
							disabled={submitting}
						/>
						<div
							className={`self-end text-xs ${isOverLimit ? "text-destructive" : "text-muted-foreground"}`}
						>
							{form.content.length} / {contentMaxLength}
						</div>
					</div>

					<div className="flex items-center gap-2">
						<span className="text-sm font-medium">当否</span>
						<CommentOutcomeSelect
							value={form.outcome}
							ariaLabel="当否"
							disabled={submitting}
							onChange={(outcome) => onFormChange({ outcome })}
						/>
						<span className="text-xs text-muted-foreground">
							レース後に入力（条件が満たされなかった条件付きコメントは判定不能）
						</span>
					</div>

					{errorMessage !== null && (
						<AlertError errors={[errorMessage]} title="エラー" />
					)}

					<div className="flex justify-end gap-2">
						{isEditing && (
							<Button variant="outline" onClick={onCancelEdit} disabled={submitting}>
								編集をやめる
							</Button>
						)}
						<Button
							onClick={onSubmit}
							disabled={submitting || isEmpty || isOverLimit}
						>
							{isEditing ? "保存" : "追加"}
						</Button>
					</div>
				</section>
			</DialogContent>
		</Dialog>
	);
};

export default EntryCommentModal;

export type { EntryCommentFormValues, EntryCommentModalProps } from "./types";
