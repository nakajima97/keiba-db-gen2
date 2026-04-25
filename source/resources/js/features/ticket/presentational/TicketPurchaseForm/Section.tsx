type SectionProps = {
	title: string;
	children: React.ReactNode;
};

export function Section({ title, children }: SectionProps) {
	return (
		<section className="space-y-3">
			<h2 className="text-sm font-semibold text-muted-foreground uppercase tracking-wide">
				{title}
			</h2>
			<div>{children}</div>
		</section>
	);
}
