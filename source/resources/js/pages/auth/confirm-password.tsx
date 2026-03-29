import { Form, Head } from "@inertiajs/react";
import InputError from "@/components/presentational/InputError";
import PasswordInput from "@/components/presentational/PasswordInput";
import { Button } from "@/components/shadcn/ui/button";
import { Label } from "@/components/shadcn/ui/label";
import { Spinner } from "@/components/shadcn/ui/spinner";
import { store } from "@/routes/password/confirm";

export default function ConfirmPassword() {
	return (
		<>
			<Head title="Confirm password" />

			<Form {...store.form()} resetOnSuccess={["password"]}>
				{({ processing, errors }) => (
					<div className="space-y-6">
						<div className="grid gap-2">
							<Label htmlFor="password">Password</Label>
							<PasswordInput
								id="password"
								name="password"
								placeholder="Password"
								autoComplete="current-password"
								autoFocus
							/>

							<InputError message={errors.password} />
						</div>

						<div className="flex items-center">
							<Button
								className="w-full"
								disabled={processing}
								data-test="confirm-password-button"
							>
								{processing && <Spinner />}
								Confirm password
							</Button>
						</div>
					</div>
				)}
			</Form>
		</>
	);
}

ConfirmPassword.layout = {
	title: "Confirm your password",
	description:
		"This is a secure area of the application. Please confirm your password before continuing.",
};
