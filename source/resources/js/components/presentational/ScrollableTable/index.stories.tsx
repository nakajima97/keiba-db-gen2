import type { Meta, StoryObj } from "@storybook/react-vite";
import ScrollableTable from ".";

const meta: Meta<typeof ScrollableTable> = {
	title: "components/presentational/ScrollableTable",
	component: ScrollableTable,
};

export default meta;
type Story = StoryObj<typeof ScrollableTable>;

const sampleChildren = (
	<>
		<thead>
			<tr className="border-b bg-muted/50">
				<th className="px-4 py-3 text-left font-medium text-muted-foreground">日付</th>
				<th className="px-4 py-3 text-left font-medium text-muted-foreground">レース場</th>
				<th className="px-4 py-3 text-left font-medium text-muted-foreground">レース番号</th>
				<th className="px-4 py-3 text-left font-medium text-muted-foreground">券種</th>
				<th className="px-4 py-3 text-left font-medium text-muted-foreground">買い方</th>
				<th className="px-4 py-3 text-right font-medium text-muted-foreground">購入金額</th>
				<th className="px-4 py-3 text-right font-medium text-muted-foreground">払い戻し金額</th>
			</tr>
		</thead>
		<tbody>
			<tr className="border-b hover:bg-muted/30">
				<td className="px-4 py-3">2026年4月5日</td>
				<td className="px-4 py-3">東京競馬場</td>
				<td className="px-4 py-3">11R</td>
				<td className="px-4 py-3">三連単</td>
				<td className="px-4 py-3">フォーメーション</td>
				<td className="px-4 py-3 text-right">¥3,600</td>
				<td className="px-4 py-3 text-right">¥12,500</td>
			</tr>
			<tr className="border-b hover:bg-muted/30">
				<td className="px-4 py-3">2026年4月5日</td>
				<td className="px-4 py-3">中山競馬場</td>
				<td className="px-4 py-3">3R</td>
				<td className="px-4 py-3">馬連</td>
				<td className="px-4 py-3">流し</td>
				<td className="px-4 py-3 text-right">¥300</td>
				<td className="px-4 py-3 text-right">—</td>
			</tr>
		</tbody>
	</>
);

export const Default: Story = {
	name: "デフォルト",
	args: {
		children: sampleChildren,
	},
};

export const Mobile: Story = {
	name: "モバイル表示",
	globals: {
		viewport: { value: "mobile1", isRotated: false },
	},
	args: {
		children: sampleChildren,
	},
};
