import ScrollableTable from "@/components/presentational/ScrollableTable";
import type { RaceMyTicketSectionProps, Selections } from "./types";

const formatSelections = (selections: Selections): string => {
	if ("horses" in selections) {
		return selections.horses.join("/");
	}
	if ("axis" in selections) {
		const axisPart = `軸: ${selections.axis.join("/")}`;
		const othersPart = selections.others
			? ` / 相手: ${selections.others.join("/")}`
			: "";
		return `${axisPart}${othersPart}`;
	}
	return selections.columns
		.map((col, i) => `${i + 1}列目: ${col.join("/")}`)
		.join(" / ");
};

const formatAmount = (amount: number | null): string => {
	if (amount == null) {
		return "-";
	}
	return `¥${amount.toLocaleString()}`;
};

const HitBadge = ({ payout_amount }: { payout_amount: number | null }) => {
	if (payout_amount == null) {
		return <span className="text-sm text-muted-foreground">—</span>;
	}
	if (payout_amount > 0) {
		return (
			<span className="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">
				的中
			</span>
		);
	}
	return (
		<span className="inline-flex items-center rounded-full bg-muted px-2 py-0.5 text-xs font-medium text-muted-foreground">
			ハズレ
		</span>
	);
};

const RaceMyTicketSection = ({ tickets }: RaceMyTicketSectionProps) => {
	if (tickets.length === 0) {
		return null;
	}

	return (
		<div className="flex flex-col gap-2">
			<h2 className="text-base font-semibold">自分の購入馬券</h2>
			<ScrollableTable>
				<thead>
					<tr className="border-b bg-muted/50">
						<th className="px-4 py-3 text-left font-medium text-muted-foreground">
							券種
						</th>
						<th className="px-4 py-3 text-left font-medium text-muted-foreground">
							買い方
						</th>
						<th className="px-4 py-3 text-left font-medium text-muted-foreground">
							選択馬番
						</th>
						<th className="px-4 py-3 text-right font-medium text-muted-foreground">
							購入金額
						</th>
						<th className="px-4 py-3 text-right font-medium text-muted-foreground">
							払戻金額
						</th>
						<th className="px-4 py-3 text-left font-medium text-muted-foreground">
							当落
						</th>
					</tr>
				</thead>
				<tbody>
					{tickets.map((ticket) => (
						<tr
							key={ticket.id}
							className="border-b last:border-0 hover:bg-muted/30"
						>
							<td className="px-4 py-3">{ticket.ticket_type_label}</td>
							<td className="px-4 py-3">{ticket.buy_type_label}</td>
							<td className="px-4 py-3">
								{formatSelections(ticket.selections)}
							</td>
							<td className="px-4 py-3 text-right">
								{formatAmount(ticket.purchase_amount)}
							</td>
							<td className="px-4 py-3 text-right">
								{formatAmount(ticket.payout_amount)}
							</td>
							<td className="px-4 py-3">
								<HitBadge payout_amount={ticket.payout_amount} />
							</td>
						</tr>
					))}
				</tbody>
			</ScrollableTable>
		</div>
	);
};

export default RaceMyTicketSection;

export type { RaceMyTicketSectionProps } from "./types";
