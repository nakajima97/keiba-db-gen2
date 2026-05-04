import { Head } from "@inertiajs/react";
import BackButton from "@/components/presentational/BackButton";
import ScrollableTable from "@/components/presentational/ScrollableTable";
import { Button } from "@/components/shadcn/ui/button";
import { Input } from "@/components/shadcn/ui/input";
import { show as raceShow } from "@/routes/races";

const RacesEntriesAdd = () => {
	const raceUid = "MOCK_RACE_UID";

	return (
		<>
			<Head title="出走馬個別追加" />
			<div className="mx-auto max-w-2xl space-y-8 p-4 lg:max-w-4xl lg:p-6">
				<div>
					<BackButton
						label="レース詳細へ戻る"
						href={raceShow.url({ race: raceUid })}
					/>
				</div>

				<h1 className="text-xl font-semibold">出走馬個別追加</h1>

				<ScrollableTable>
					<tbody>
						<tr className="border-b">
							<th className="w-32 bg-muted/50 px-4 py-3 text-left font-medium text-muted-foreground">
								開催日
							</th>
							<td className="px-4 py-3">2024年5月1日</td>
						</tr>
						<tr className="border-b">
							<th className="bg-muted/50 px-4 py-3 text-left font-medium text-muted-foreground">
								競馬場
							</th>
							<td className="px-4 py-3">東京</td>
						</tr>
						<tr>
							<th className="bg-muted/50 px-4 py-3 text-left font-medium text-muted-foreground">
								レース番号
							</th>
							<td className="px-4 py-3">1R</td>
						</tr>
					</tbody>
				</ScrollableTable>

				<form className="space-y-6">
					<div className="space-y-2">
						<label
							htmlFor="horse_name"
							className="text-sm font-medium text-foreground"
						>
							馬名
						</label>
						<Input id="horse_name" type="text" placeholder="例：ディープインパクト" />
					</div>

					<div className="space-y-2">
						<label
							htmlFor="jockey_name"
							className="text-sm font-medium text-foreground"
						>
							騎手名
						</label>
						<Input id="jockey_name" type="text" placeholder="例：武豊" />
					</div>

					<div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
						<div className="space-y-2">
							<label
								htmlFor="frame_number"
								className="text-sm font-medium text-foreground"
							>
								枠番（1〜8）
							</label>
							<Input
								id="frame_number"
								type="number"
								min={1}
								max={8}
								placeholder="1"
							/>
						</div>

						<div className="space-y-2">
							<label
								htmlFor="horse_number"
								className="text-sm font-medium text-foreground"
							>
								馬番（1〜18）
							</label>
							<Input
								id="horse_number"
								type="number"
								min={1}
								max={18}
								placeholder="1"
							/>
						</div>
					</div>

					<div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
						<div className="space-y-2">
							<label
								htmlFor="weight"
								className="text-sm font-medium text-foreground"
							>
								負担重量（kg）
							</label>
							<Input
								id="weight"
								type="number"
								step="0.1"
								placeholder="55.0"
							/>
						</div>

						<div className="space-y-2">
							<label
								htmlFor="horse_weight"
								className="text-sm font-medium text-foreground"
							>
								馬体重（kg、任意）
							</label>
							<Input
								id="horse_weight"
								type="number"
								placeholder="480"
							/>
						</div>
					</div>

					<div className="flex gap-3 pt-2">
						<Button type="submit" className="flex-1">
							追加
						</Button>
					</div>
				</form>
			</div>
		</>
	);
};

export default RacesEntriesAdd;
