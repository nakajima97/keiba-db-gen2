import { Link } from "@inertiajs/react";
import { Button } from "@/components/shadcn/ui/button";
import { Input } from "@/components/shadcn/ui/input";
import { Label } from "@/components/shadcn/ui/label";
import ScrollableTable from "@/components/presentational/ScrollableTable";
import { show } from "@/routes/horses";
import type { HorseSearchProps } from "./types";

const HorseSearch = ({
	horses,
	keyword,
	searchedKeyword,
	onKeywordChange,
	onSearch,
}: HorseSearchProps) => {
	return (
		<div className="flex flex-col gap-4">
			<h1 className="text-xl font-semibold">競走馬検索</h1>

			<form
				className="flex flex-wrap items-end gap-2"
				onSubmit={(e) => {
					e.preventDefault();
					onSearch();
				}}
			>
				<div className="flex flex-col gap-1.5">
					<Label htmlFor="horse-search-keyword">馬名</Label>
					<Input
						id="horse-search-keyword"
						type="search"
						value={keyword}
						onChange={(e) => onKeywordChange(e.target.value)}
						placeholder="馬名の一部を入力"
						className="w-64"
					/>
				</div>
				<Button type="submit">検索</Button>
			</form>

			{searchedKeyword === null ? (
				<p className="py-16 text-center text-muted-foreground">
					馬名を入力して検索してください
				</p>
			) : horses.length === 0 ? (
				<p className="py-16 text-center text-muted-foreground">
					該当する競走馬が見つかりません
				</p>
			) : (
				<ScrollableTable>
					<thead>
						<tr className="border-b bg-muted/50">
							<th className="px-4 py-3 text-left font-medium text-muted-foreground">
								馬名
							</th>
							<th className="px-4 py-3 text-left font-medium text-muted-foreground">
								生年
							</th>
						</tr>
					</thead>
					<tbody>
						{horses.map((horse) => (
							<tr
								key={horse.uid}
								className="border-b last:border-0 hover:bg-muted/30"
							>
								<td className="px-4 py-3">
									<Link
										href={show.url({ horse: horse.uid })}
										className="underline-offset-4 hover:underline"
									>
										{horse.name}
									</Link>
								</td>
								<td className="px-4 py-3">
									{horse.birth_year !== null ? `${horse.birth_year}年` : "—"}
								</td>
							</tr>
						))}
					</tbody>
				</ScrollableTable>
			)}
		</div>
	);
};

export default HorseSearch;

export type { HorseSearchItem, HorseSearchProps } from "./types";
