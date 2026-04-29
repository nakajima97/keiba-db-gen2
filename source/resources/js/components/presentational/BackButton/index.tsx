import { Link } from "@inertiajs/react";
import { ArrowLeft } from "lucide-react";
import { Button } from "@/components/shadcn/ui/button";

type Props = {
	label: string;
	href?: string;
};

const BackButton = ({ label, href }: Props) => {
	if (href !== undefined) {
		return (
			<Button asChild variant="outline" size="sm">
				<Link href={href}>
					<ArrowLeft />
					{label}
				</Link>
			</Button>
		);
	}

	return (
		<Button
			type="button"
			variant="outline"
			size="sm"
			onClick={() => window.history.back()}
		>
			<ArrowLeft />
			{label}
		</Button>
	);
};

export default BackButton;
