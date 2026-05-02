import { Link } from "@inertiajs/react";
import { Button } from "@/components/shadcn/ui/button";
import { Spinner } from "@/components/shadcn/ui/spinner";
import ScrollableTable from "@/components/presentational/ScrollableTable";
import { newMethod } from "@/routes/tickets";
import { formatDateDisplay } from "@/utils/date";
import type { TicketPurchaseListProps } from "./types";
import { formatSelections } from "./utils";

const TicketPurchaseList = ({
	purchases,
	hasMore,
	isLoading,
	onLoadMore,
}: TicketPurchaseListProps) => {
	return (
		<div className="flex flex-col gap-4 p-4">
			<div className="flex items-center justify-between">
				<h1 className="text-xl font-semibold">購入馬券一覧</h1>
				{purchases.length > 0 && (
					<Link href={newMethod.url()}>馬券を登録する</Link>
				)}
			</div>

			{purchases.length === 0 ? (
				<div className="flex flex-col items-center justify-center gap-4 py-16 text-muted-foreground">
					<p>まだ購入記録がありません</p>
					<Link href={newMethod.url()}>馬券を登録する</Link>
				</div>
			) : (
				<>
					<ScrollableTable>
						<thead>
							<tr className="border-b bg-muted/50">
								<th className="px-4 py-3 text-left font-medium text-muted-foreground">
									日付
								</th>
								<th className="px-4 py-3 text-left font-medium text-muted-foreground">
									レース場
								</th>
								<th className="px-4 py-3 text-left font-medium text-muted-foreground">
									レース番号
								</th>
								<th className="px-4 py-3 text-left font-medium text-muted-foreground">
									券種
								</th>
								<th className="px-4 py-3 text-left font-medium text-muted-foreground">
									買い方
								</th>
								<th className="px-4 py-3 text-right font-medium text-muted-foreground">
									点数
								</th>
								<th className="px-4 py-3 text-right font-medium text-muted-foreground">
									購入金額
								</th>
								<th className="px-4 py-3 text-right font-medium text-muted-foreground">
									払い戻し金額
								</th>
								<th className="px-4 py-3 text-left font-medium text-muted-foreground">
									レース結果
								</th>
							</tr>
						</thead>
						<tbody>
							{purchases.map((purchase) => (
								<tr
									key={purchase.id}
									className="border-b last:border-0 hover:bg-muted/30"
								>
									<td className="px-4 py-3">
										{purchase.race_date
											? formatDateDisplay(purchase.race_date)
											: "-"}
									</td>
									<td className="px-4 py-3">{purchase.venue_name ?? "-"}</td>
									<td className="px-4 py-3">{purchase.race_number ?? "-"}</td>
									<td className="px-4 py-3">{purchase.ticket_type_label}</td>
									<td className="px-4 py-3">
										{formatSelections(purchase.selections)}
									</td>
									<td className="px-4 py-3 text-right">
										{purchase.num_combinations}
									</td>
									<td className="px-4 py-3 text-right">
										{purchase.purchase_amount != null
											? `¥${purchase.purchase_amount.toLocaleString()}`
											: "-"}
									</td>
									<td className="px-4 py-3 text-right">
										{purchase.payout_amount != null
											? `¥${purchase.payout_amount.toLocaleString()}`
											: "-"}
									</td>
									<td className="px-4 py-3">
										{purchase.race_uid != null ? (
											purchase.has_race_result ? (
												<Link
													href={`/races/${purchase.race_uid}/result/edit`}
													className="text-sm underline"
												>
													確認・編集
												</Link>
											) : (
												<Link
													href={`/races/${purchase.race_uid}/result/new`}
													className="text-sm underline"
												>
													結果入力
												</Link>
											)
										) : (
											"-"
										)}
									</td>
								</tr>
							))}
						</tbody>
					</ScrollableTable>

					{hasMore && (
						<div className="flex justify-center">
							<Button
								variant="outline"
								onClick={onLoadMore}
								disabled={isLoading}
							>
								{isLoading ? (
									<>
										<Spinner className="mr-2" />
										読み込み中...
									</>
								) : (
									"もっと読み込む"
								)}
							</Button>
						</div>
					)}
				</>
			)}
		</div>
	);
};

export default TicketPurchaseList;

export type { TicketPurchaseListItem, TicketPurchaseListProps } from "./types";
