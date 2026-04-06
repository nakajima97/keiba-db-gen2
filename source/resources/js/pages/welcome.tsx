import { Head, Link, usePage } from "@inertiajs/react";
import { dashboard, login, register } from "@/routes";

export default function Welcome({
	canRegister = true,
}: {
	canRegister?: boolean;
}) {
	const { auth } = usePage().props;

	return (
		<>
			<Head title="Welcome" />
			<div className="flex min-h-screen flex-col bg-background text-foreground">
				<header className="w-full p-6 lg:p-8">
					<nav className="flex items-center justify-end gap-4">
						{auth.user ? (
							<Link
								href={dashboard()}
								className="inline-block rounded-sm border border-border px-5 py-1.5 text-sm leading-normal hover:bg-accent"
							>
								Dashboard
							</Link>
						) : (
							<>
								<Link
									href={login()}
									className="inline-block rounded-sm px-5 py-1.5 text-sm leading-normal hover:underline"
								>
									Log in
								</Link>
								{canRegister && (
									<Link
										href={register()}
										className="inline-block rounded-sm border border-border px-5 py-1.5 text-sm leading-normal hover:bg-accent"
									>
										Register
									</Link>
								)}
							</>
						)}
					</nav>
				</header>
				<main className="flex flex-1 items-center justify-center p-6 lg:p-8">
					<div className="text-center">
						<h1 className="mb-4 text-2xl font-semibold">keiba-db</h1>
						<p className="text-muted-foreground">
							個人利用目的の競馬データベース
						</p>
					</div>
				</main>
			</div>
		</>
	);
}
