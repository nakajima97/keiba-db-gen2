import type { FormEvent } from "react";
import InputError from "@/components/presentational/InputError";
import { Button } from "@/components/shadcn/ui/button";
import { Input } from "@/components/shadcn/ui/input";
import type { RaceNominationAddFormProps } from "./types";

const formatCandidateLabel = (name: string, birthYear: number | null) =>
	birthYear !== null ? `${name}（${birthYear}年生）` : name;

const RaceNominationAddForm = ({
	value,
	candidates,
	submitting,
	errorMessage,
	onValueChange,
	onCandidateSelect,
	onSubmit,
}: RaceNominationAddFormProps) => {
	const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
		event.preventDefault();
		if (value.trim() === "" || submitting) {
			return;
		}
		onSubmit();
	};

	return (
		<form onSubmit={handleSubmit} className="flex flex-col gap-1">
			<div className="flex gap-2">
				<div className="relative w-full max-w-xs">
					<Input
						value={value}
						onChange={(event) => onValueChange(event.target.value)}
						placeholder="馬名を入力して Enter"
						aria-label="登録馬の馬名"
						autoComplete="off"
						disabled={submitting}
					/>
					{candidates.length > 0 && (
						<ul
							aria-label="競走馬の候補"
							className="absolute z-20 mt-1 max-h-60 w-full overflow-y-auto rounded-md border bg-background shadow-md"
						>
							{candidates.map((candidate) => (
								<li key={candidate.id}>
									<button
										type="button"
										className="w-full px-3 py-2 text-left text-sm hover:bg-muted"
										onClick={() => onCandidateSelect(candidate)}
										disabled={submitting}
									>
										{formatCandidateLabel(candidate.name, candidate.birth_year)}
									</button>
								</li>
							))}
						</ul>
					)}
				</div>
				<Button
					type="submit"
					variant="outline"
					disabled={submitting || value.trim() === ""}
				>
					追加
				</Button>
			</div>
			<InputError message={errorMessage ?? undefined} />
		</form>
	);
};

export default RaceNominationAddForm;

export type { RaceNominationAddFormProps } from "./types";
