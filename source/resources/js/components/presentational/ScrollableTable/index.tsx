import type { ReactNode } from "react";

type Props = {
	children: ReactNode;
};

const ScrollableTable = ({ children }: Props) => {
	return (
		<div className="overflow-x-auto rounded-xl border">
			<table className="w-full min-w-max text-sm">{children}</table>
		</div>
	);
};

export default ScrollableTable;
